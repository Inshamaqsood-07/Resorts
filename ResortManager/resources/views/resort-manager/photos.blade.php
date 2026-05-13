@extends('layouts.resort-manager.master')
@section('page_title', 'Resort Photos')

@section('content')
<div class="page-header">
  <p>Upload and manage your resort's photo gallery. Photos are stored in the public/images folder.</p>
</div>

{{-- Upload Form --}}
<div class="table-card" style="padding:24px;margin-bottom:24px;">
  <h5 style="margin-bottom:18px;"><i class="bi bi-cloud-upload" style="color:var(--primary);margin-right:8px;"></i>Upload New Photo</h5>
  <form method="POST" action="{{ route('manager.photos.upload') }}" enctype="multipart/form-data" id="uploadForm">
    @csrf
    <div style="display:grid;grid-template-columns:2fr 2fr 1fr auto;gap:16px;align-items:end;">
      <div class="form-group" style="margin-bottom:0;">
        <label>Photo <span style="color:var(--danger);">*</span></label>
        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp" required id="photoInput"/>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label>Caption</label>
        <input type="text" name="caption" class="form-control" placeholder="Optional caption..."/>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-top:28px;">
          <input type="checkbox" name="is_cover" value="1" style="width:16px;height:16px;accent-color:var(--primary);"/>
          Set as Cover
        </label>
      </div>
      <button type="submit" class="btn-primary" style="margin-bottom:0;white-space:nowrap;">
        <i class="bi bi-upload"></i> Upload
      </button>
    </div>
    {{-- Preview --}}
    <div id="previewWrap" style="display:none;margin-top:16px;">
      <img id="preview" style="height:120px;border-radius:8px;border:1px solid var(--border);"/>
    </div>
  </form>
</div>

{{-- Photo Grid --}}
@if($photos->count() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;">
  @foreach($photos as $photo)
  <div style="background:var(--surface);border-radius:var(--radius);overflow:hidden;border:1px solid var(--border);box-shadow:var(--shadow);">
    <div style="position:relative;height:150px;overflow:hidden;">
      <img src="{{ asset($photo->photo_url) }}" alt="{{ $photo->caption }}"
           style="width:100%;height:100%;object-fit:cover;"
           onerror="this.src='https://placehold.co/400x250?text=Photo'"/>
      @if($photo->is_cover)
        <span style="position:absolute;top:8px;left:8px;background:var(--primary);color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;">COVER</span>
      @endif
    </div>
    <div style="padding:10px 12px;">
      @if($photo->caption)
        <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $photo->caption }}</div>
      @endif
      <div style="display:flex;gap:6px;">
        @if(!$photo->is_cover)
        <form method="POST" action="{{ route('manager.photos.cover',$photo) }}" style="flex:1;">@csrf
          <button class="btn-outline" style="width:100%;padding:5px 8px;font-size:11px;">Set Cover</button>
        </form>
        @endif
        <form method="POST" action="{{ route('manager.photos.delete',$photo) }}" onsubmit="return confirm('Delete this photo?');">
          @csrf @method('DELETE')
          <button class="btn-danger" style="padding:5px 10px;font-size:11px;"><i class="bi bi-trash"></i></button>
        </form>
      </div>
    </div>
  </div>
  @endforeach
</div>
@else
<div style="text-align:center;padding:60px;color:var(--text-muted);">
  <i class="bi bi-images" style="font-size:48px;display:block;margin-bottom:12px;opacity:.3;"></i>
  No photos uploaded yet. Upload your first photo above.
</div>
@endif

<script>
document.getElementById('photoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('preview').src = e.target.result;
      document.getElementById('previewWrap').style.display = 'block';
    };
    reader.readAsDataURL(file);
  }
});
</script>
@endsection
