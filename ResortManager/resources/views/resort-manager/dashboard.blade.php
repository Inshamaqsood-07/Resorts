@extends('layouts.resort-manager.master')
@section('page_title', 'Dashboard')

@section('content')
<div class="page-header">
  <p>Welcome back, {{ auth()->user()->full_name }}. Here's your resort overview.</p>
</div>

@if(!$resort)
<div class="alert alert-warning">
  <i class="bi bi-exclamation-triangle-fill"></i>
  Your resort details are not set up yet. <a href="{{ route('manager.resort') }}" style="font-weight:700;color:inherit;">Complete your resort profile &rarr;</a>
</div>
@elseif($resort->status === 'pending')
<div class="alert alert-warning">
  <i class="bi bi-hourglass-split"></i>
  Your resort is <strong>pending admin approval</strong>. It will appear on the website once approved.
</div>
@elseif($resort->status === 'suspended')
<div class="alert alert-danger">
  <i class="bi bi-slash-circle"></i>
  Your resort has been <strong>suspended</strong>. Please contact admin for more information.
</div>
@endif

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--primary-light);"><i class="bi bi-calendar-check-fill" style="color:var(--primary);"></i></div>
      <div><div class="stat-label">Total Bookings</div><div class="stat-value">{{ number_format($stats['total_bookings'] ?? 0) }}</div><div class="stat-sub">All time</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--warning-light);"><i class="bi bi-hourglass-split" style="color:var(--warning);"></i></div>
      <div><div class="stat-label">Pending</div><div class="stat-value">{{ number_format($stats['pending_bookings'] ?? 0) }}</div><div class="stat-sub">Awaiting action</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--success-light);"><i class="bi bi-check-circle-fill" style="color:var(--success);"></i></div>
      <div><div class="stat-label">Confirmed</div><div class="stat-value">{{ number_format($stats['confirmed_bookings'] ?? 0) }}</div><div class="stat-sub">This month</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--purple-light);"><i class="bi bi-cash-stack" style="color:var(--purple);"></i></div>
      <div><div class="stat-label">Revenue</div><div class="stat-value">PKR {{ number_format($stats['monthly_revenue'] ?? 0) }}</div><div class="stat-sub">This month</div></div>
    </div>
  </div>
</div>

<div class="table-card">
  <div class="table-card-header">
    <h5>Recent Bookings</h5>
    <a href="{{ route('manager.bookings') }}" style="font-size:12px;color:var(--accent);font-weight:600;">View All &rarr;</a>
  </div>
  <div class="table-responsive-wrap">
    <table>
      <thead><tr><th>Reference</th><th>Guest</th><th>Check-In</th><th>Check-Out</th><th>Nights</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($recentBookings as $b)
        <tr>
          <td style="font-weight:700;color:var(--primary);">{{ $b->booking_reference }}</td>
          <td>{{ $b->client?->full_name }}</td>
          <td>{{ $b->check_in_date?->format('M d, Y') }}</td>
          <td>{{ $b->check_out_date?->format('M d, Y') }}</td>
          <td>{{ $b->total_nights }}</td>
          <td style="font-weight:700;">PKR {{ number_format($b->total_amount) }}</td>
          <td><span class="badge-status bs-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
          <td>
            @if($b->status === 'pending')
              <div style="display:flex;gap:6px;">
                <form method="POST" action="{{ route('manager.bookings.confirm',$b) }}">@csrf<button class="btn-success">Confirm</button></form>
                <button class="btn-danger" onclick="openModal('cancel-{{ $b->id }}')">Cancel</button>
              </div>
            @else <span style="color:var(--text-sm);font-size:12px;">—</span> @endif
          </td>
        </tr>
        <div class="modal-overlay" id="cancel-{{ $b->id }}"><div class="modal-box">
          <div class="modal-title">Cancel Booking</div>
          <div class="modal-sub">Reference: {{ $b->booking_reference }}</div>
          <form method="POST" action="{{ route('manager.bookings.cancel',$b) }}">@csrf
            <div class="form-group"><label>Cancellation Reason</label><textarea name="reason" class="form-control" rows="3" required></textarea></div>
            <div class="modal-actions"><button type="button" class="btn-outline" onclick="closeModal('cancel-{{ $b->id }}')">Back</button><button type="submit" class="btn-danger">Cancel Booking</button></div>
          </form>
        </div></div>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text-muted);">No bookings yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});});
</script>
@endsection
