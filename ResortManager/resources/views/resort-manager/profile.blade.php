@extends('layouts.resort-manager.master')

@section('page_title', 'My Profile')

@section('content')

<style>
.pw-wrap { position:relative; display:block; }
.pw-wrap input { padding-right:42px !important; }
.eye-btn { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:16px; padding:0; z-index:5; }
.eye-btn:hover { color:var(--primary); }
.resend-btn { background:none; border:none; color:var(--accent); font-size:12px; font-weight:600; cursor:pointer; margin-top:8px; }
.resend-btn:disabled { opacity:0.5; cursor:not-allowed; }
.timer-text { font-size:11px; color:var(--text-muted); margin-top:4px; }
</style>

<div class="page-header"><p>Update your personal information and account settings.</p></div>

<div class="row g-3">

    <!-- Profile Info - FIXED: Added enctype -->
    <div class="col-md-6">
        <div class="table-card" style="padding:24px;">
            <h5 style="margin-bottom:20px;"><i class="bi bi-person-circle" style="color:var(--primary);margin-right:8px;"></i>Personal Information</h5>
            
            <!-- IMPORTANT: enctype="multipart/form-data" is required for file upload -->
            <form method="POST" action="{{ route('manager.profile.update') }}" enctype="multipart/form-data" id="profileForm">
                @csrf
                <div class="form-group">
                    <label>Profile Photo</label>
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:8px;flex-wrap:wrap;">
                        @if($user->managerProfile?->profile_photo && file_exists(public_path('images/profiles/' . $user->managerProfile->profile_photo)))
                            <img src="{{ asset('images/profiles/' . $user->managerProfile->profile_photo) }}" 
                                 style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" 
                                 onerror="this.style.display='none'"/>
                        @else
                            <div style="width:64px;height:64px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;">
                                {{ strtoupper(substr($user->full_name,0,1)) }}
                            </div>
                        @endif
                        <div style="flex:1;">
                            <input type="file" name="profile_photo" id="profile_photo" 
                                   accept="image/jpeg,image/png,image/jpg,image/webp" 
                                   class="form-control" 
                                   style="padding:8px;"/>
                            <small class="text-muted" style="font-size:11px;">Allowed: JPG, PNG, JPEG, WEBP (Max 2MB)</small>
                        </div>
                    </div>
                    @error('profile_photo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->full_name) }}" required/>
                    @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="{{ $user->email }}" disabled style="background:var(--bg);cursor:not-allowed;"/>
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">To change email, use the Change Email section below.</div>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}"/>
                    @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                
                <div class="form-group">
                    <label>CNIC</label>
                    <input type="text" class="form-control" value="{{ $user->managerProfile?->cnic_number }}" disabled style="background:var(--bg);"/>
                </div>
                
                <button type="submit" class="btn-primary" id="saveProfileBtn">
                    <i class="bi bi-save"></i> Save Profile
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-6" style="display:flex;flex-direction:column;gap:16px;">
        <!-- Change Password -->
        <div class="table-card" style="padding:24px;">
            <h5 style="margin-bottom:20px;"><i class="bi bi-lock-fill" style="color:var(--primary);margin-right:8px;"></i>Change Password</h5>
            <form method="POST" action="{{ route('manager.change.password') }}" id="pwForm">@csrf
                <div class="form-group">
                    <label>Current Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="current_password" id="mCurPw" class="form-control {{ $errors->has('current_password')?'is-invalid':'' }}" required/>
                        <button type="button" class="eye-btn" onclick="togglePw('mCurPw',this)"><i class="bi bi-eye"></i></button>
                    </div>
                    @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="npw" class="form-control" required minlength="8"/>
                        <button type="button" class="eye-btn" onclick="togglePw('npw',this)"><i class="bi bi-eye"></i></button>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="pw-wrap">
                        <input type="password" name="password_confirmation" id="cpw" class="form-control" required/>
                        <button type="button" class="eye-btn" onclick="togglePw('cpw',this)"><i class="bi bi-eye"></i></button>
                    </div>
                    <div class="invalid-feedback" id="cpwErr"></div>
                </div>
                <button type="submit" class="btn-primary"><i class="bi bi-shield-lock"></i> Change Password</button>
            </form>
        </div>

        <!-- Change Email -->
        <div class="table-card" style="padding:24px;">
            <h5 style="margin-bottom:8px;"><i class="bi bi-envelope-fill" style="color:var(--primary);margin-right:8px;"></i>Change Email</h5>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:18px;">Current: <strong>{{ $user->email }}</strong></p>

            @if(session('pending_new_email'))
                <div class="alert alert-warning"><i class="bi bi-info-circle"></i> OTP sent to your new email. Verify below.</div>
                <form method="POST" action="{{ route('manager.otp.change-email.confirm') }}" id="confirmNewForm">
                    @csrf
                    <div class="form-group">
                        <label>OTP (sent to new email)</label>
                        <input type="text" name="otp" class="form-control {{ $errors->has('otp')?'is-invalid':'' }}" maxlength="6" required/>
                        @error('otp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-primary"><i class="bi bi-check2-circle"></i> Confirm New Email</button>
                </form>
                <form method="POST" action="{{ route('manager.change.email.otp') }}" id="resendNewForm">
                    @csrf
                    <input type="hidden" name="new_email" value="{{ session('pending_new_email') }}">
                    <button type="submit" class="resend-btn" id="resendNewBtn"><i class="bi bi-arrow-repeat"></i> Resend OTP to new email</button>
                </form>
                <div id="resendNewTimer" class="timer-text"></div>
                <div class="text-center mt-2"><a href="{{ route('manager.profile') }}" class="small text-decoration-none">← Start Over</a></div>

            @elseif(session('success') && str_contains(session('success'), 'OTP sent'))
                <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> OTP sent to your current email.</div>
                <form method="POST" action="{{ route('manager.change.email.verify') }}" id="otpVerifyForm">
                    @csrf
                    <input type="hidden" name="new_email" value="{{ session('new_email_pending', old('new_email')) }}">
                    <div class="form-group">
                        <label>Enter OTP</label>
                        <input type="text" name="otp" class="form-control {{ $errors->has('otp')?'is-invalid':'' }}" maxlength="6" required/>
                        @error('otp')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-primary">Verify OTP</button>
                </form>
                <form method="POST" action="{{ route('manager.change.email.otp') }}" id="resendForm">
                    @csrf
                    <input type="hidden" name="new_email" value="{{ session('new_email_pending', old('new_email')) }}">
                    <button type="submit" class="resend-btn" id="resendBtn"><i class="bi bi-arrow-repeat"></i> Resend OTP</button>
                </form>
                <div id="resendTimer" class="timer-text"></div>

            @else
                <form method="POST" action="{{ route('manager.change.email.otp') }}" id="emailStep1">
                    @csrf
                    <div class="form-group">
                        <label>New Email Address</label>
                        <input type="email" name="new_email" class="form-control {{ $errors->has('new_email')?'is-invalid':'' }}" required/>
                        @error('new_email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-primary"><i class="bi bi-send"></i> Send OTP</button>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
// Password match validation
document.getElementById('pwForm')?.addEventListener('submit', function(e) {
    if (document.getElementById('npw').value !== document.getElementById('cpw').value) {
        e.preventDefault();
        document.getElementById('cpw').classList.add('is-invalid');
        document.getElementById('cpwErr').textContent = 'Passwords do not match.';
    }
});

// Toggle password visibility
function togglePw(id, btn) {
    var inp = document.getElementById(id);
    var icon = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}

// Show loading state on profile save
document.getElementById('profileForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('saveProfileBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Saving...';
    }
});

// Resend OTP cooldown functions
function setupResendCooldown(formId, btnId, timerId, storageKey) {
    const form = document.getElementById(formId);
    if (!form) return;
    
    const btn = document.getElementById(btnId);
    const timerDiv = document.getElementById(timerId);
    
    function startCooldown(seconds) {
        btn.disabled = true;
        let remaining = seconds;
        timerDiv.innerHTML = `Wait ${remaining} seconds before resending...`;
        const interval = setInterval(function() {
            remaining--;
            timerDiv.innerHTML = `Wait ${remaining} seconds before resending...`;
            if (remaining <= 0) {
                clearInterval(interval);
                btn.disabled = false;
                timerDiv.innerHTML = '';
            }
        }, 1000);
    }
    
    const lastResend = localStorage.getItem(storageKey);
    if (lastResend) {
        const elapsed = Math.floor((Date.now() - parseInt(lastResend)) / 1000);
        if (elapsed < 60) {
            startCooldown(60 - elapsed);
        } else {
            localStorage.removeItem(storageKey);
        }
    }
    
    form.addEventListener('submit', function() {
        localStorage.setItem(storageKey, Date.now());
        startCooldown(60);
    });
}

setupResendCooldown('resendForm', 'resendBtn', 'resendTimer', 'manager_email_resend');
setupResendCooldown('resendNewForm', 'resendNewBtn', 'resendNewTimer', 'manager_email_resend_new');
</script>

@endsection