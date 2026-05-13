@extends('layouts.client.master')

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

<div class="page-header"><p>Manage your personal information and account settings.</p></div>

<div class="row g-3">

    <!-- Personal Info -->
    <div class="col-md-6">
        <div class="table-card" style="padding:24px;">
            <h5 style="margin-bottom:20px;"><i class="bi bi-person-circle" style="color:var(--primary);margin-right:8px;"></i>Personal Information</h5>
            <form method="POST" action="{{ route('client.profile.update') }}" enctype="multipart/form-data">@csrf
                <div class="form-group">
                    <label>Profile Photo</label>
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:8px;">
                        @if($user->clientProfile?->profile_photo)
                            <img src="{{ asset('images/profiles/' . $user->clientProfile->profile_photo) }}" style="width:64px;height:64px;border-radius:50%;object-fit:cover;border:2px solid var(--border);" onerror="this.style.display='none'"/>
                        @else
                            <div style="width:64px;height:64px;border-radius:50%;background:var(--primary);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:800;color:#fff;">
                                {{ strtoupper(substr($user->full_name,0,1)) }}
                            </div>
                        @endif
                        <input type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" class="form-control" style="max-width:260px;"/>
                    </div>
                </div>
                <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" class="form-control" value="{{ old('full_name',$user->full_name) }}" required/></div>
                <div class="form-group"><label>Email</label><input type="email" class="form-control" value="{{ $user->email }}" disabled style="background:var(--bg);"/><div style="font-size:11px;color:var(--text-muted);margin-top:4px;">Change email in Account Settings below.</div></div>
                <div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="{{ old('phone',$user->phone) }}"/></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div class="form-group"><label>Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control" 
                            value="{{ old('date_of_birth', $user->clientProfile && $user->clientProfile->date_of_birth ? \Carbon\Carbon::parse($user->clientProfile->date_of_birth)->format('Y-m-d') : '') }}"/>
                    </div>
                    <div class="form-group"><label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">Select</option>
                            @foreach(['Male','Female','Other','Prefer not to say'] as $g)
                                <option value="{{ $g }}" {{ old('gender',$user->clientProfile?->gender)===$g?'selected':'' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Nationality</label><input type="text" name="nationality" class="form-control" value="{{ old('nationality',$user->clientProfile?->nationality) }}"/></div>
                    <div class="form-group"><label>City</label><input type="text" name="city" class="form-control" value="{{ old('city',$user->clientProfile?->city) }}"/></div>
                    <div class="form-group"><label>Country</label><input type="text" name="country" class="form-control" value="{{ old('country',$user->clientProfile?->country) }}"/></div>
                </div>
                <div class="form-group"><label>Address</label><textarea name="address" class="form-control" rows="2">{{ old('address',$user->clientProfile?->address) }}</textarea></div>
                <button type="submit" class="btn-primary"><i class="bi bi-save"></i> Save Profile</button>
            </form>
        </div>
    </div>

    <div class="col-md-6" style="display:flex;flex-direction:column;gap:16px;">
        <!-- Change Password -->
        <div class="table-card" style="padding:24px;">
            <h5 style="margin-bottom:20px;"><i class="bi bi-lock-fill" style="color:var(--primary);margin-right:8px;"></i>Change Password</h5>
            <form method="POST" action="{{ route('client.change.password') }}" id="cpwForm">@csrf
                <div class="form-group"><label>Current Password</label><div class="pw-wrap"><input type="password" name="current_password" id="cCurPw" class="form-control {{ $errors->has('current_password')?'is-invalid':'' }}" required/><button type="button" class="eye-btn" onclick="togglePw('cCurPw',this)"><i class="bi bi-eye"></i></button></div>
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="form-group"><label>New Password</label><div class="pw-wrap"><input type="password" name="password" id="cnpw" class="form-control" required minlength="8"/><button type="button" class="eye-btn" onclick="togglePw('cnpw',this)"><i class="bi bi-eye"></i></button></div></div>
                <div class="form-group"><label>Confirm Password</label><div class="pw-wrap"><input type="password" name="password_confirmation" id="ccpw" class="form-control" required/><button type="button" class="eye-btn" onclick="togglePw('ccpw',this)"><i class="bi bi-eye"></i></button></div><div class="invalid-feedback" id="ccpwErr"></div></div>
                <button type="submit" class="btn-primary"><i class="bi bi-shield-lock"></i> Change Password</button>
            </form>
        </div>

        <!-- Change Email -->
        <div class="table-card" style="padding:24px;">
            <h5 style="margin-bottom:8px;"><i class="bi bi-envelope-fill" style="color:var(--primary);margin-right:8px;"></i>Change Email</h5>
            <p style="font-size:12px;color:var(--text-muted);margin-bottom:18px;">Current: <strong>{{ $user->email }}</strong></p>

            @if(session('pending_new_email'))
                <!-- Step 3: Confirm new email OTP -->
                <div class="alert alert-warning"><i class="bi bi-info-circle"></i> OTP sent to your new email. Verify below.</div>
                <form method="POST" action="{{ route('client.otp.change-email.confirm') }}" id="confirmNewForm">
                    @csrf
                    <div class="form-group">
                        <label>OTP (sent to new email)</label>
                        <input type="text" name="otp" class="form-control {{ $errors->has('otp')?'is-invalid':'' }}" maxlength="6" required/>
                        @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-primary"><i class="bi bi-check2-circle"></i> Confirm New Email</button>
                </form>
                <!-- Resend OTP to new email - UPDATED -->
                <form method="POST" action="{{ route('client.change.email.resend.new') }}" id="resendNewForm">
                    @csrf
                    <button type="submit" class="resend-btn" id="resendNewBtn">
                        <i class="bi bi-arrow-repeat"></i> Resend OTP to new email
                    </button>
                </form>
                <div id="resendNewTimer" class="timer-text"></div>
                <div class="text-center mt-2"><a href="{{ route('client.profile') }}" class="small text-decoration-none">← Start Over</a></div>

            @elseif(session('success') && str_contains(session('success'), 'OTP sent'))
                <!-- Step 2: Verify OTP from current email -->
                <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> OTP sent to your current email.</div>
                <form method="POST" action="{{ route('client.change.email.verify') }}" id="otpVerifyForm">
                    @csrf
                    <input type="hidden" name="new_email" value="{{ session('new_email_pending', old('new_email')) }}">
                    <div class="form-group">
                        <label>Enter OTP</label>
                        <input type="text" name="otp" class="form-control {{ $errors->has('otp')?'is-invalid':'' }}" maxlength="6" required/>
                        @error('otp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-primary">Verify OTP</button>
                </form>
                <!-- Resend OTP to current email - UPDATED -->
                <form method="POST" action="{{ route('client.change.email.resend') }}" id="resendForm">
                    @csrf
                    <input type="hidden" name="new_email" value="{{ session('new_email_pending', old('new_email')) }}">
                    <button type="submit" class="resend-btn" id="resendBtn">
                        <i class="bi bi-arrow-repeat"></i> Resend OTP
                    </button>
                </form>
                <div id="resendTimer" class="timer-text"></div>

            @else
                <!-- Step 1: Enter new email -->
                <form method="POST" action="{{ route('client.change.email.otp') }}" id="emailStep1">
                    @csrf
                    <div class="form-group">
                        <label>New Email Address</label>
                        <input type="email" name="new_email" class="form-control {{ $errors->has('new_email')?'is-invalid':'' }}" required/>
                        @error('new_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn-primary"><i class="bi bi-send"></i> Send OTP</button>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
// Password match validation
document.getElementById('cpwForm')?.addEventListener('submit', function(e) {
    if (document.getElementById('cnpw').value !== document.getElementById('ccpw').value) {
        e.preventDefault();
        document.getElementById('ccpw').classList.add('is-invalid');
        document.getElementById('ccpwErr').textContent = 'Passwords do not match.';
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

// Resend OTP cooldown function
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

// Setup resend cooldowns
setupResendCooldown('resendForm', 'resendBtn', 'resendTimer', 'client_email_resend');
setupResendCooldown('resendNewForm', 'resendNewBtn', 'resendNewTimer', 'client_email_resend_new');
</script>

@endsection