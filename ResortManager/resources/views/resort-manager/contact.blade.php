@extends('layouts.resort-manager.master')
@section('page_title', 'Contact Admin')

@section('content')
<div class="page-header"><p>Send a message to the ResortSphere admin team.</p></div>

<div style="max-width:680px;">
  <div class="table-card" style="padding:32px;">
    @if($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill"></i> Please fill in all required fields.</div>
    @endif

    <form method="POST" action="{{ route('manager.contact.submit') }}" id="contactForm" novalidate>
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="sender_name" class="form-control" value="{{ auth()->user()->full_name }}" required/>
        </div>
        <div class="form-group">
          <label>Email Address *</label>
          <input type="email" name="sender_email" class="form-control" value="{{ auth()->user()->email }}" required/>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input type="tel" name="sender_phone" class="form-control" value="{{ auth()->user()->phone }}"/>
        </div>
        <div class="form-group">
          <label>Subject *</label>
          <select name="subject" class="form-control" required>
            <option value="">— Select —</option>
            @foreach(['Resort Approval Query','Booking Issue','Technical Problem','Account Query','Other'] as $s)
              <option value="{{ $s }}">{{ $s }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Message *</label>
        <textarea name="message" class="form-control {{ $errors->has('message') ? 'is-invalid' : '' }}" rows="5" required maxlength="2000" placeholder="Describe your issue or question..."></textarea>
      </div>
      <button type="submit" class="btn-primary"><i class="bi bi-send"></i> Send Message</button>
    </form>
  </div>
</div>
@endsection
