@extends('layouts.client.master')
@section('page_title', 'Dashboard')

@section('content')
<div class="page-header">
  <h4>Welcome, {{ auth()->user()->full_name }}</h4>
  <p>Here's a summary of your booking activity on ResortSphere.</p>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--primary-light);"><i class="bi bi-calendar2-check-fill" style="color:var(--primary);"></i></div>
      <div><div class="stat-label">Total Bookings</div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-sub">All time</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--success-light);"><i class="bi bi-check-circle-fill" style="color:var(--success);"></i></div>
      <div><div class="stat-label">Confirmed</div><div class="stat-value">{{ $stats['confirmed'] }}</div><div class="stat-sub">Upcoming stays</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--warning-light);"><i class="bi bi-hourglass-split" style="color:var(--warning);"></i></div>
      <div><div class="stat-label">Pending</div><div class="stat-value">{{ $stats['pending'] }}</div><div class="stat-sub">Awaiting confirmation</div></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:var(--danger-light);"><i class="bi bi-x-circle-fill" style="color:var(--danger);"></i></div>
      <div><div class="stat-label">Cancelled</div><div class="stat-value">{{ $stats['cancelled'] }}</div><div class="stat-sub">All time</div></div>
    </div>
  </div>
</div>

<div class="table-card">
  <div class="table-card-header">
    <h5>Recent Bookings</h5>
    <a href="{{ route('client.bookings') }}" style="font-size:12px;color:var(--accent);font-weight:600;">View All &rarr;</a>
  </div>
  <div class="table-responsive-wrap">
    <table>
      <thead><tr><th>Reference</th><th>Resort</th><th>Room</th><th>Check-In</th><th>Check-Out</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($recentBookings as $b)
        <tr>
          <td style="font-weight:700;color:var(--primary);">{{ $b->booking_reference }}</td>
          <td style="font-weight:600;">{{ $b->resort?->name }}</td>
          <td style="color:var(--text-muted);">{{ $b->room?->name }}</td>
          <td>{{ $b->check_in_date?->format('M d, Y') }}</td>
          <td>{{ $b->check_out_date?->format('M d, Y') }}</td>
          <td style="font-weight:700;">PKR {{ number_format($b->total_amount) }}</td>
          <td><span class="badge-status bs-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted);">You have no bookings yet. <a href="{{ route('home') }}" style="color:var(--accent);font-weight:600;">Browse Resorts &rarr;</a></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
