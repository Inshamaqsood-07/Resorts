@extends('layouts.resort-manager.master')
@section('page_title', 'Manage Bookings')

@section('content')
<div class="page-header"><p>Confirm or cancel incoming booking requests.</p></div>

<div class="filter-bar">
  <form method="GET" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;width:100%;">
    <div class="filter-select-wrap">
      <label>Status:</label>
      <select name="status" onchange="this.form.submit()">
        <option value="">All</option>
        <option value="pending" {{ request('status')==='pending' ? 'selected' : '' }}>Pending</option>
        <option value="confirmed" {{ request('status')==='confirmed' ? 'selected' : '' }}>Confirmed</option>
        <option value="cancelled" {{ request('status')==='cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="completed" {{ request('status')==='completed' ? 'selected' : '' }}>Completed</option>
      </select>
    </div>
    <a href="{{ route('manager.bookings') }}" class="btn-reset">Reset</a>
  </form>
</div>

<div class="table-card">
  <div class="table-card-header">
    <h5>All Bookings - {{ $resort->name }}</h5>
  </div>
  <div class="table-responsive-wrap">
    <table>
      <thead>
        <tr>
          <th>Reference</th><th>Guest</th><th>Room</th>
          <th>Check-In</th><th>Check-Out</th>
          <th>Amount</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @if($bookings->count() > 0)
        @foreach($bookings as $b)
        <tr>
          <td style="font-weight:700;color:var(--primary);">{{ $b->booking_reference }}</td>
          <td>
            <div style="font-weight:600;">{{ $b->client?->full_name }}</div>
            <div style="font-size:11px;color:var(--text-muted);">{{ $b->client?->email }}</div>
          </td>
          <td>{{ $b->room?->name }}</td>
          <td>{{ $b->check_in_date?->format('M d, Y') }}</td>
          <td>{{ $b->check_out_date?->format('M d, Y') }}</td>
          <td style="font-weight:700;">PKR {{ number_format($b->total_amount) }}</td>
          <td><span class="badge-status bs-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
          <td>
            @if($b->status === 'pending')
            <div style="display:flex;gap:6px;">
              <form method="POST" action="{{ route('manager.bookings.confirm', $b) }}">
                @csrf
                <button class="btn-success">Confirm</button>
              </form>
              <button class="btn-danger" onclick="openModal('mc{{ $b->id }}')">Cancel</button>
            </div>
            @else
            <span style="color:var(--text-sm);font-size:12px;">-</span>
            @endif
          </td>
        </tr>
        @endforeach
        @else
        <tr>
          <td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted);">No bookings found.</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>
  <!-- PAGINATION - SMALL BUTTONS -->
  <div class="table-footer" style="text-align:center;">
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
</div>
@if($bookings->count() > 0)
@foreach($bookings as $b)
@if($b->status === 'pending')
<div class="modal-overlay" id="mc{{ $b->id }}">
  <div class="modal-box">
    <div class="modal-title">Cancel Booking</div>
    <div class="modal-sub">Ref: {{ $b->booking_reference }}</div>
    <form method="POST" action="{{ route('manager.bookings.cancel', $b) }}">
      @csrf
      <div class="form-group">
        <label>Reason *</label>
        <textarea name="reason" class="form-control" rows="3" required></textarea>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="closeModal('mc{{ $b->id }}')">Back</button>
        <button type="submit" class="btn-danger">Cancel</button>
      </div>
    </form>
  </div>
</div>
@endif
@endforeach
@endif

<script>
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(m) {
  m.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
  });
});
</script>

@endsection
