@extends('layouts.client.master')
@section('page_title', 'Book Resort')

@section('content')
<div class="page-header">
  <h4>Book Your Stay</h4>
  <p>{{ $resort->name }} &mdash; {{ $room->name }}</p>
</div>

<div class="row g-3">
  {{-- Booking Form --}}
  <div class="col-md-7">
    <div class="table-card" style="padding:28px;">
      <h5 style="margin-bottom:20px;"><i class="bi bi-calendar-plus" style="color:var(--primary);margin-right:8px;"></i>Booking Details</h5>
      <form method="POST" action="{{ route('client.book.store', ['resort' => $resort->id, 'room' => $room->id]) }}" id="bookForm" novalidate>
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div class="form-group">
            <label>Check-In Date *</label>
            <input type="date" name="check_in_date" id="checkIn" class="form-control {{ $errors->has('check_in_date')?'is-invalid':'' }}"
                   min="{{ date('Y-m-d') }}" required value="{{ old('check_in_date') }}" onchange="calcTotal()"/>
            @error('check_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Check-Out Date *</label>
            <input type="date" name="check_out_date" id="checkOut" class="form-control {{ $errors->has('check_out_date')?'is-invalid':'' }}"
                   min="{{ date('Y-m-d',strtotime('+1 day')) }}" required value="{{ old('check_out_date') }}" onchange="calcTotal()"/>
            @error('check_out_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="form-group">
            <label>Adults *</label>
            <select name="guests_adults" class="form-control" required>
              @for($i=1;$i<=$room->max_occupancy;$i++)
                <option value="{{ $i }}" {{ old('guests_adults')==$i?'selected':'' }}>{{ $i }}</option>
              @endfor
            </select>
          </div>
          <div class="form-group">
            <label>Children</label>
            <select name="guests_children" class="form-control">
              @for($i=0;$i<=4;$i++)<option value="{{ $i }}" {{ old('guests_children')==$i?'selected':'' }}>{{ $i }}</option>@endfor
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Special Requests</label>
          <textarea name="special_requests" class="form-control" rows="3" placeholder="Any special requests or preferences...">{{ old('special_requests') }}</textarea>
        </div>

        {{-- Price Summary --}}
        <div style="background:var(--primary-light);border-radius:var(--radius-sm);padding:16px;margin-bottom:20px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;">
            <span>PKR {{ number_format($room->price_per_night) }} &times; <span id="nightsCount">0</span> night(s)</span>
            <span style="font-weight:700;" id="totalDisplay">PKR 0</span>
          </div>
          <div style="border-top:1px solid rgba(0,0,0,.1);padding-top:8px;display:flex;justify-content:space-between;font-weight:800;font-size:15px;color:var(--primary);">
            <span>Total Amount</span>
            <span id="grandTotal">PKR 0</span>
          </div>
        </div>

        <div id="availabilityMsg" style="display:none;padding:12px 16px;border-radius:8px;font-size:13px;font-weight:600;margin-bottom:12px;"></div>

        <button type="submit" class="btn-primary" id="bookBtn" style="width:100%;padding:13px;font-size:15px;">
          <i class="bi bi-check2-circle"></i> Confirm Booking Request
        </button>
      </form>
    </div>
  </div>

  {{-- Resort & Room Summary --}}
  <div class="col-md-5">
    <div class="table-card" style="overflow:hidden;">
      @php $cover = $resort->photos->where('is_cover',true)->first() ?? $resort->photos->first(); @endphp
      @if($cover)
        <img src="{{ asset($cover->photo_url) }}" style="width:100%;height:180px;object-fit:cover;" onerror="this.style.display='none'"/>
      @endif
      <div style="padding:20px;">
        <div style="font-size:17px;font-weight:800;margin-bottom:4px;">{{ $resort->name }}</div>
        <div style="font-size:13px;color:var(--text-muted);margin-bottom:14px;">
          <i class="bi bi-geo-alt"></i> {{ $resort->location?->city }}, {{ $resort->location?->country }}
        </div>
        <div style="border-top:1px solid var(--border);padding-top:14px;">
          <div style="font-weight:700;margin-bottom:8px;">{{ $room->name }}</div>
          <div style="display:flex;flex-direction:column;gap:6px;font-size:13px;color:var(--text-muted);">
            <div><i class="bi bi-people" style="margin-right:6px;"></i>Max {{ $room->max_occupancy }} guests</div>
            @if($room->bed_type)<div><i class="bi bi-moon" style="margin-right:6px;"></i>{{ $room->bed_type }} bed</div>@endif
            @if($room->size_sqft)<div><i class="bi bi-arrows-fullscreen" style="margin-right:6px;"></i>{{ $room->size_sqft }} sqft</div>@endif
            <div style="margin-top:8px;font-size:16px;font-weight:800;color:var(--primary);">PKR {{ number_format($room->price_per_night) }}<span style="font-size:12px;font-weight:500;color:var(--text-muted);">/night</span></div>
          </div>
        </div>
        @if($resort->cancellation_policy)
        <div style="margin-top:14px;padding:10px;background:var(--bg);border-radius:6px;font-size:12px;color:var(--text-muted);">
          <strong>Cancellation Policy:</strong> {{ $resort->cancellation_policy }}
        </div>
        @endif
        @if($resort->check_in_time)
        <div style="margin-top:10px;font-size:12px;color:var(--text-muted);">
          <i class="bi bi-clock"></i> Check-in: {{ $resort->check_in_time }} &nbsp;|&nbsp; Check-out: {{ $resort->check_out_time }}
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<script>
const pricePerNight = {{ $room->price_per_night }};

function calcTotal() {
  const ci = document.getElementById('checkIn').value;
  const co = document.getElementById('checkOut').value;
  if (!ci || !co) return;
  const diff = (new Date(co) - new Date(ci)) / (1000*60*60*24);
  if (diff <= 0) {
    document.getElementById('checkOut').classList.add('is-invalid');
    return;
  }
  document.getElementById('checkOut').classList.remove('is-invalid');
  const total = diff * pricePerNight;
  document.getElementById('nightsCount').textContent = diff;
  document.getElementById('totalDisplay').textContent = 'PKR ' + total.toLocaleString();
  document.getElementById('grandTotal').textContent   = 'PKR ' + total.toLocaleString();

  // Check availability
  checkAvailability();
}

function checkAvailability() {
  var ci = document.getElementById('checkIn').value;
  var co = document.getElementById('checkOut').value;
  if (!ci || !co) return;

  var msg = document.getElementById('availabilityMsg');
  var btn = document.getElementById('bookBtn');

  msg.style.display = 'block';
  msg.style.background = '#f1f5f9';
  msg.style.color = '#64748b';
  msg.textContent = 'Checking availability...';

  fetch('{{ route("client.check.availability", [$resort->id, $room->id]) }}?check_in=' + ci + '&check_out=' + co)
    .then(r => r.json())
    .then(data => {
      if (data.available) {
        msg.style.background = '#f0fdf4';
        msg.style.color = '#166534';
        msg.style.border = '1px solid #bbf7d0';
        msg.innerHTML = '<i class="bi bi-check-circle-fill"></i> Room is available for selected dates!';
        btn.disabled = false;
        btn.style.opacity = '1';
      } else {
        msg.style.background = '#fef2f2';
        msg.style.color = '#991b1b';
        msg.style.border = '1px solid #fecaca';
        msg.innerHTML = '<i class="bi bi-x-circle-fill"></i> Sorry, this room is not available for selected dates.';
        btn.disabled = true;
        btn.style.opacity = '0.6';
      }
    });
}

document.getElementById('bookForm').addEventListener('submit', function(e) {
  const ci = document.getElementById('checkIn').value;
  const co = document.getElementById('checkOut').value;
  if (!ci || !co || new Date(co) <= new Date(ci)) {
    e.preventDefault();
    document.getElementById('checkOut').classList.add('is-invalid');
    alert('Check-out date must be after check-in date.');
  }
});
</script>
@endsection
