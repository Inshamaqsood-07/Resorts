@extends('layouts.client.master')

@section('page_title', 'My Bookings')

@section('content')

<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
  <div>
    <p>View and manage all your resort bookings.</p>
  </div>
  <a href="{{ route('home') }}" class="btn-primary">
    <i class="bi bi-search"></i> Browse Resorts
  </a>
</div>

<div class="filter-bar">
  <form method="GET" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;width:100%;">
    <div class="filter-select-wrap">
      <label>Status:</label>
      <select name="status" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="pending"   {{ request('status')=='pending'   ? 'selected' : '' }}>Pending</option>
        <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Confirmed</option>
        <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
      </select>
    </div>
    <a href="{{ route('client.bookings') }}" class="btn-reset">
      <i class="bi bi-arrow-counterclockwise"></i> Reset
    </a>
  </form>
</div>

@if($bookings->count() > 0)
@foreach($bookings as $b)
<div class="table-card" style="margin-bottom:16px;padding:20px;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
    <div style="flex:1;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
        <span style="font-weight:800;color:var(--primary);font-size:15px;">{{ $b->booking_reference }}</span>
        <span class="badge-status bs-{{ $b->status }}">{{ ucfirst($b->status) }}</span>
      </div>
      <div style="font-size:16px;font-weight:700;margin-bottom:4px;">{{ $b->resort?->name }}</div>
      <div style="font-size:13px;color:var(--text-muted);margin-bottom:10px;">{{ $b->room?->name }}</div>
      <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:13px;">
        <div><div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-sm);margin-bottom:2px;">Check-In</div><div style="font-weight:600;">{{ $b->check_in_date?->format('M d, Y') }}</div></div>
        <div><div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-sm);margin-bottom:2px;">Check-Out</div><div style="font-weight:600;">{{ $b->check_out_date?->format('M d, Y') }}</div></div>
        <div><div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-sm);margin-bottom:2px;">Nights</div><div style="font-weight:600;">{{ $b->total_nights }}</div></div>
        <div><div style="font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-sm);margin-bottom:2px;">Amount</div><div style="font-weight:800;color:var(--primary);font-size:15px;">PKR {{ number_format($b->total_amount) }}</div></div>
      </div>
      @if($b->status === 'cancelled' && $b->cancellation_reason)
      <div style="margin-top:10px;font-size:12px;color:var(--danger);padding:8px 12px;background:var(--danger-light);border-radius:6px;">
        <strong>Cancellation Reason:</strong> {{ $b->cancellation_reason }}
      </div>
      @endif
    </div>
    
    <div style="flex-shrink:0;">
      <!-- FIXED: Cancellation allowed for confirmed bookings too (24 hours before check-in) -->
      @if($b->canBeCancelledByClient())
        <button class="btn-danger" onclick="openModal('modal{{ $b->id }}')">
          <i class="bi bi-x-circle"></i> Cancel
        </button>
      @endif
    </div>
  </div>
</div>
@endforeach

<!-- PAGINATION - SMALL BUTTONS -->
<div style="margin-top:20px; text-align:center;">
    @if($bookings->onFirstPage())
      <span style="display:inline-block; padding:4px 10px; font-size:12px; background:#f1f5f9; color:#94a3b8; border-radius:4px; margin:0 2px;">← Previous</span>
    @else
      <a href="{{ $bookings->previousPageUrl() }}" style="display:inline-block; padding:4px 10px; font-size:12px; background:#fff; color:#1a4fa0; border-radius:4px; text-decoration:none; margin:0 2px; border:1px solid #e2e8f0;">← Previous</a>
    @endif
    
    <span style="display:inline-block; padding:4px 10px; font-size:12px; background:#f1f5f9; color:#64748b; border-radius:4px; margin:0 4px;">
      Page {{ $bookings->currentPage() }} of {{ $bookings->lastPage() }}
    </span>
    
    @if($bookings->hasMorePages())
      <a href="{{ $bookings->nextPageUrl() }}" style="display:inline-block; padding:4px 10px; font-size:12px; background:#fff; color:#1a4fa0; border-radius:4px; text-decoration:none; margin:0 2px; border:1px solid #e2e8f0;">Next →</a>
    @else
      <span style="display:inline-block; padding:4px 10px; font-size:12px; background:#f1f5f9; color:#94a3b8; border-radius:4px; margin:0 2px;">Next →</span>
    @endif
</div>

@foreach($bookings as $b)
@if($b->canBeCancelledByClient())
<div class="modal-overlay" id="modal{{ $b->id }}">
  <div class="modal-box">
    <div class="modal-title">Cancel Booking</div>
    <div class="modal-sub">Booking: <strong>{{ $b->booking_reference }}</strong></div>
    <form method="POST" action="{{ route('client.bookings.cancel', $b) }}">
      @csrf
      <div class="form-group">
        <label>Reason *</label>
        <textarea name="reason" class="form-control" rows="3" required></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="closeModal('modal{{ $b->id }}')">Keep Booking</button>
        <button type="submit" class="btn-danger">Yes, Cancel</button>
      </div>
    </form>
  </div>
</div>
@endif
@endforeach

@else
<div style="text-align:center;padding:60px;color:var(--text-muted);">
  <i class="bi bi-calendar-x" style="font-size:48px;display:block;margin-bottom:12px;opacity:.3;"></i>
  <div style="font-size:16px;font-weight:700;margin-bottom:8px;">No bookings found.</div>
  <a href="{{ route('home') }}" style="color:var(--accent);font-weight:600;">Browse and book a resort &rarr;</a>
</div>
@endif

<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
document.querySelectorAll('.modal-overlay').forEach(function(m){
  m.addEventListener('click', function(e){
    if (e.target === this) this.classList.remove('open');
  });
});
</script>

@endsection