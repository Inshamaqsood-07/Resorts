@extends('layouts.resort-manager.master')

@section('page_title', 'My Resort')

@section('content')

<div class="page-header">
    <p>Update your resort information and amenities.</p>
</div>


@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:20px;">
        <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger" style="margin-bottom:20px;">
        <i class="bi bi-exclamation-circle-fill"></i> 
        @foreach($errors->all() as $error)
            {{ $error }}<br>
        @endforeach
    </div>
@endif

@if($resort)
<div style="display:inline-flex;align-items:center;gap:8px;margin-bottom:20px;padding:8px 16px;background:var(--surface);border:1px solid var(--border);border-radius:20px;">
    <span style="font-size:12px;color:var(--text-muted);">Resort Status:</span>
    <span class="badge-status bs-{{ $resort->status }}">{{ ucfirst($resort->status) }}</span>
</div>
@endif

<form method="POST" action="{{ route('manager.resort.update') }}" id="resortForm">
    @csrf
    <div class="row g-3">
        <!-- Basic Info -->
        <div class="col-md-8">
            <div class="table-card" style="padding:24px;">
                <h5 style="margin-bottom:20px;">
                    <i class="bi bi-building" style="color:var(--primary);margin-right:8px;"></i>Basic Information
                </h5>
                <div class="form-group">
                    <label>Resort Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control {{ $errors->has('name')?'is-invalid':'' }}" 
                           value="{{ old('name', $resort?->name) }}" required/>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <input type="hidden" name="category" value="Resort"/>
                    <div class="form-group">
                        <label>City <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="city" class="form-control" 
                               value="{{ old('city', $resort?->location?->city) }}" required/>
                    </div>
                    <div class="form-group">
                        <label>Country <span style="color:var(--danger);">*</span></label>
                        <input type="text" name="country" class="form-control" 
                               value="{{ old('country', $resort?->location?->country) }}" required/>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" 
                               value="{{ old('address', $resort?->location?->address) }}"/>
                    </div>
                    <div class="form-group">
                        <label>Check-in Time</label>
                        <input type="time" name="check_in_time" class="form-control" 
                               value="{{ old('check_in_time', $resort?->check_in_time ?? '14:00') }}"/>
                    </div>
                    <div class="form-group">
                        <label>Check-out Time</label>
                        <input type="time" name="check_out_time" class="form-control" 
                               value="{{ old('check_out_time', $resort?->check_out_time ?? '12:00') }}"/>
                    </div>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" 
                              placeholder="Tell guests about your resort...">{{ old('description', $resort?->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Cancellation Policy</label>
                    <textarea name="cancellation_policy" class="form-control" rows="3" 
                              placeholder="e.g. Free cancellation up to 48 hours before check-in.">{{ old('cancellation_policy', $resort?->cancellation_policy) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Amenities - FIXED -->
        <div class="col-md-4">
            <div class="table-card" style="padding:24px;">
                <h5 style="margin-bottom:16px;">
                    <i class="bi bi-star" style="color:var(--primary);margin-right:8px;"></i>Amenities
                </h5>
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;">
                    Select all amenities your resort offers.
                </p>
                
                @php
                    $selectedAmenities = $resort && $resort->relationLoaded('amenities') 
                        ? $resort->amenities->pluck('id')->toArray() 
                        : ($resort ? $resort->amenities()->pluck('amenity_id')->toArray() : []);
                @endphp
                
                <div style="display:flex;flex-direction:column;gap:10px;max-height:400px;overflow-y:auto;">
                    @foreach($amenities as $amenity)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:13px;padding:8px;border-radius:6px;transition:background .15s;" 
                           onmouseover="this.style.background='var(--bg)'" 
                           onmouseout="this.style.background='transparent'">
                        <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}"
                            {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}
                            style="width:16px;height:16px;accent-color:var(--primary);flex-shrink:0;"/>
                        <i class="bi {{ $amenity->icon ?? 'bi-check2' }}" style="color:var(--primary);font-size:16px;"></i>
                        <span>{{ $amenity->name }}</span>
                    </label>
                    @endforeach
                </div>
                
                @if($amenities->isEmpty())
                    <p class="text-muted" style="text-align:center;padding:20px;">No amenities found in database.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Save button -->
    <div style="margin-top:20px;">
        <button type="submit" class="btn-primary" style="padding:12px 32px;font-size:15px;" id="saveBtn">
            <i class="bi bi-save"></i> Save All Changes
        </button>
    </div>
</form>

<script>
// Optional: Show loading state on submit
document.getElementById('resortForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
});
</script>

@endsection