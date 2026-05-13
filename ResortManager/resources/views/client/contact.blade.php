@extends('layouts.client.master')

@section('page_title', 'Contact Admin')

@section('content')

<div class="page-header"><p>Send us a message and we'll get back to you within 24 hours.</p></div>

<div style="max-width:680px;">
  <div class="table-card" style="padding:32px;">
    
    @if($errors->any())
      <div class="alert alert-danger">
        <i class="bi bi-exclamation-circle-fill"></i> Please fill in all required fields.
      </div>
    @endif

    <!-- DIFFERENT ACTION - Client specific controller -->
    <form method="POST" action="{{ route('client.contact.submit') }}" novalidate>
      @csrf
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="sender_name" class="form-control" value="{{ auth()->user()->full_name }}" required/>
        </div>
        
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="sender_email" class="form-control" value="{{ auth()->user()->email }}" required/>
        </div>
        
        <div class="form-group">
          <label>Phone</label>
          <input type="tel" name="sender_phone" class="form-control" value="{{ auth()->user()->phone }}"/>
        </div>
        
        <div class="form-group">
          <label>Subject *</label>
          <select name="subject" class="form-control" required>
            <option value="">--- Select Subject ---</option>
            <option value="Booking Inquiry">Booking Inquiry</option>
            <option value="Cancellation Request">Cancellation Request</option>
            <option value="Complaint">Complaint</option>
            <option value="Resort Information">Resort Information</option>
            <option value="Other">Other</option>
          </select>
        </div>
        
      </div>
      
      <div class="form-group">
        <label>Message *</label>
        <textarea name="message" class="form-control" rows="5" required maxlength="2000" placeholder="Write your message here..."></textarea>
      </div>
      
      <button type="submit" class="btn-primary">
        <i class="bi bi-send"></i> Send Message
      </button>
    </form>
  </div>
</div>

@endsection