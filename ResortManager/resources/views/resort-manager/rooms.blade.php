@extends('layouts.resort-manager.master')
@section('page_title', 'Rooms')

@section('content')
<div class="page-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
  <div><p>Manage your resort's room types and pricing.</p></div>
  <button class="btn-primary" onclick="openModal('addRoom')"><i class="bi bi-plus-lg"></i> Add Room</button>
</div>

{{-- Rooms List --}}
@forelse($rooms as $room)
<div class="table-card" style="margin-bottom:16px;padding:20px;">
  <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;">
    <div style="flex:1;">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
        <span style="font-size:16px;font-weight:700;">{{ $room->name }}</span>
        <span class="badge-status {{ $room->is_active ? 'bs-approved' : 'bs-suspended' }}">{{ $room->is_active ? 'Active' : 'Inactive' }}</span>
        @if($room->room_type)<span style="background:var(--primary-light);color:var(--primary);padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">{{ ucfirst($room->room_type) }}</span>@endif
      </div>
      <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:13px;color:var(--text-muted);">
        <span><i class="bi bi-cash-coin" style="margin-right:4px;"></i>PKR {{ number_format($room->price_per_night) }}/night</span>
        <span><i class="bi bi-people" style="margin-right:4px;"></i>Max {{ $room->max_occupancy }} guests</span>
        <span><i class="bi bi-door-open" style="margin-right:4px;"></i>{{ $room->total_units }} unit(s)</span>
        @if($room->bed_type)<span><i class="bi bi-moon" style="margin-right:4px;"></i>{{ ucfirst($room->bed_type) }} bed</span>@endif
        @if($room->size_sqft)<span><i class="bi bi-arrows-fullscreen" style="margin-right:4px;"></i>{{ $room->size_sqft }} sqft</span>@endif
      </div>
      @if($room->description)
        <p style="font-size:13px;color:var(--text-muted);margin-top:8px;">{{ $room->description }}</p>
      @endif
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0;">
      <button class="btn-outline" onclick="openEditModal({{ $room->id }})"><i class="bi bi-pencil"></i> Edit</button>
      <form method="POST" action="{{ route('manager.rooms.delete',$room) }}" onsubmit="return confirm('Delete this room?');">
        @csrf @method('DELETE')
        <button class="btn-danger"><i class="bi bi-trash"></i></button>
      </form>
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editRoom-{{ $room->id }}">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-title">Edit Room</div>
    <form method="POST" action="{{ route('manager.rooms.update',$room) }}">@csrf @method('PUT')
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group"><label>Room Name *</label><input type="text" name="name" class="form-control" value="{{ $room->name }}" required/></div>
        <div class="form-group"><label>Room Type</label>
          <select name="room_type" class="form-control">
            @foreach(['Standard','Deluxe','Suite','Family','Presidential'] as $t)
              <option value="{{ $t }}" {{ $room->room_type===$t?'selected':'' }}>{{ $t }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group"><label>Price/Night (PKR) *</label><input type="number" name="price_per_night" class="form-control" value="{{ $room->price_per_night }}" required min="0"/></div>
        <div class="form-group"><label>Max Occupancy *</label><input type="number" name="max_occupancy" class="form-control" value="{{ $room->max_occupancy }}" required min="1"/></div>
        <div class="form-group"><label>Total Units *</label><input type="number" name="total_units" class="form-control" value="{{ $room->total_units }}" required min="1"/></div>
        <div class="form-group"><label>Bed Type</label>
          <select name="bed_type" class="form-control">
            <option value="">Select</option>
            @foreach(['Single','Double','Twin','King','Queen'] as $bt)
              <option value="{{ $bt }}" {{ $room->bed_type===$bt?'selected':'' }}>{{ $bt }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group"><label>Size (sqft)</label><input type="number" name="size_sqft" class="form-control" value="{{ $room->size_sqft }}" min="0"/></div>
        <div class="form-group"><label>Status</label>
          <select name="is_active" class="form-control">
            <option value="1" {{ $room->is_active?'selected':'' }}>Active</option>
            <option value="0" {{ !$room->is_active?'selected':'' }}>Inactive</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2">{{ $room->description }}</textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="closeModal('editRoom-{{ $room->id }}')">Cancel</button>
        <button type="submit" class="btn-primary"><i class="bi bi-save"></i> Save</button>
      </div>
    </form>
  </div>
</div>
@empty
<div style="text-align:center;padding:60px;color:var(--text-muted);">
  <i class="bi bi-door-open" style="font-size:48px;display:block;margin-bottom:12px;opacity:.3;"></i>
  No rooms added yet. Click "Add Room" to get started.
</div>
@endforelse

{{-- Add Room Modal --}}
<div class="modal-overlay" id="addRoom">
  <div class="modal-box" style="max-width:560px;">
    <div class="modal-title">Add New Room</div>
    <form method="POST" action="{{ route('manager.rooms.add') }}">@csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div class="form-group"><label>Room Name *</label><input type="text" name="name" class="form-control" required/></div>
        <div class="form-group"><label>Room Type</label>
          <select name="room_type" class="form-control">
            @foreach(['Standard','Deluxe','Suite','Family','Presidential'] as $t)
              <option value="{{ $t }}">{{ $t }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group"><label>Price/Night (PKR) *</label><input type="number" name="price_per_night" class="form-control" required min="0"/></div>
        <div class="form-group"><label>Max Occupancy *</label><input type="number" name="max_occupancy" class="form-control" value="2" required min="1"/></div>
        <div class="form-group"><label>Total Units *</label><input type="number" name="total_units" class="form-control" value="1" required min="1"/></div>
        <div class="form-group"><label>Bed Type</label>
          <select name="bed_type" class="form-control">
            <option value="">Select</option>
            @foreach(['Single','Double','Twin','King','Queen'] as $bt)<option value="{{ $bt }}">{{ $bt }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Size (sqft)</label><input type="number" name="size_sqft" class="form-control" min="0"/></div>
      </div>
      <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
      <div class="modal-actions">
        <button type="button" class="btn-outline" onclick="closeModal('addRoom')">Cancel</button>
        <button type="submit" class="btn-primary"><i class="bi bi-plus-lg"></i> Add Room</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.add('open');}
function closeModal(id){document.getElementById(id).classList.remove('open');}
function openEditModal(id){document.getElementById('editRoom-'+id).classList.add('open');}
document.querySelectorAll('.modal-overlay').forEach(m=>{m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('open');});});
</script>
@endsection
