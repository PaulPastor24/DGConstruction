<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - D&G Construction</title>
    <link rel="icon" type="image/png" href="{{ asset('images/D&G.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/D&G.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/D&G.png') }}">
    <meta name="msapplication-TileImage" content="{{ asset('images/D&G.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
</head>
<body>

<div class="login-shell">
    <!-- LEFT SIDE HERO OVERLAY -->
    <section class="login-hero">
        <a href="{{ url('/') }}" class="back-link" aria-label="Back to homepage">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Back to Home
        </a>

        <div class="hero-content-wrap">
            <!-- BRAND TITLE WRAPPER BLOCK -->
            <div class="hero-brand-row">
                <img class="hero-logo-left" src="{{ asset('images/D&G.png') }}" alt="D&G Construction logo">
                <span class="hero-brand-title">Development Corp.</span>
            </div>
            
            <h1>Welcome back.</h1>
            <p>
                Sign in to your portal to manage active project timelines, view structural blueprint metrics, and coordinate operations.
            </p>

            <div class="hero-points">
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Secure Access
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Clear Roles
                </span>
                <span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    Fast Operations
                </span>
            </div>
        </div>
    </section>

    <!-- RIGHT SIDE INTERACTIVE FORM PANEL -->
    <section class="login-panel">
        <div class="login-card">
            @php
                $selectedPortal = old('portal', 'staff');
                $selectedPortal = in_array($selectedPortal, ['staff', 'client'], true) ? $selectedPortal : 'staff';
            @endphp

            <div class="login-header">
                <h2>Portal Access</h2>
                <span class="subtitle">D&G Construction Management Hub</span>
            </div>

            <div class="portal-switch" id="portalSwitch" role="group" aria-label="Choose your portal">
                <button type="button" class="portal-option {{ $selectedPortal === 'staff' ? 'active' : '' }}" data-portal="staff" aria-pressed="{{ $selectedPortal === 'staff' ? 'true' : 'false' }}">Staff</button>
                <button type="button" class="portal-option {{ $selectedPortal === 'client' ? 'active' : '' }}" data-portal="client" aria-pressed="{{ $selectedPortal === 'client' ? 'true' : 'false' }}">Client</button>
            </div>
            <p class="portal-description" id="portalDescription">
                {{ $selectedPortal === 'staff' ? 'For administrators, engineers, and supervisors.' : 'For clients viewing their construction projects.' }}
            </p>

            @if (session('error'))
                <div class="login-message error-banner">
                    {{ session('error') }}
                </div>
            @endif

            <form class="login-form" id="loginForm" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <input type="email" name="email" class="form-input" id="loginEmail" placeholder="name@company.com" value="{{ old('email') }}" autocomplete="username" required>
                </div>

                <div class="form-group password-group">
                    <div class="label-row">
                        <label class="form-label" for="loginPassword">Password</label>
                    </div>
                    <div class="input-container">
                        <input type="password" name="password" class="form-input" id="loginPassword" placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="passwordToggle" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">Show</button>
                    </div>
                </div>

                <input type="hidden" id="selectedPortal" name="portal" value="{{ $selectedPortal }}">

                <button type="submit" class="login-btn" id="loginBtn">
                    <span id="loginBtnText">{{ $selectedPortal === 'staff' ? 'Sign in to Staff Portal' : 'Sign in to Client Portal' }}</span>
                </button>
            </form>

            <a id="googleClientLogin" href="{{ route('auth.google.redirect') }}" class="login-btn google-login-btn" @if($selectedPortal !== 'client') hidden @endif>
                <svg class="google-mark" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                    <path fill="#4285F4" d="M43.6 24.5c0-1.4-.1-2.8-.4-4.1H24v7.8h11a9.4 9.4 0 0 1-4.1 6.2v5.1h6.6c3.9-3.6 6.1-8.8 6.1-15Z"/>
                    <path fill="#34A853" d="M24 44c5.5 0 10.1-1.8 13.5-4.8l-6.6-5.1c-1.8 1.2-4.1 2-6.9 2-5.3 0-9.8-3.6-11.4-8.4H5.8v5.3A20 20 0 0 0 24 44Z"/>
                    <path fill="#FBBC05" d="M12.6 27.7a12 12 0 0 1 0-7.4V15H5.8a20 20 0 0 0 0 17.9l6.8-5.2Z"/>
                    <path fill="#EA4335" d="M24 11.9c3 0 5.7 1 7.8 3.1l5.9-5.9C34.1 5.8 29.5 4 24 4A20 20 0 0 0 5.8 15l6.8 5.3c1.6-4.8 6.1-8.4 11.4-8.4Z"/>
                </svg>
                <span>Continue with Google</span>
            </a>

            @if(app()->environment('local'))
            <div class="demo-credentials">
                <div class="demo-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    Quick Sandbox Access
                </div>
                <div class="demo-grid">
                    <span><strong>Eng:</strong> admin@dg-corp.ph</span>
                    <span><strong>Super:</strong> supervisor@dg-corp.ph</span>
                    <span><strong>Client:</strong> client@dg-corp.ph</span>
                    <span><strong>Pass:</strong> password123</span>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>

<script>
    function selectPortal(button, portal) {
        document.querySelectorAll('.portal-option').forEach(function (option) {
            const isActive = option === button;
            option.classList.toggle('active', isActive);
            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        document.getElementById('selectedPortal').value = portal;
        document.getElementById('portalDescription').textContent = portal === 'client'
            ? 'For clients viewing their construction projects.'
            : 'For administrators, engineers, and supervisors.';
        document.getElementById('loginBtnText').textContent = portal === 'client'
            ? 'Sign in to Client Portal'
            : 'Sign in to Staff Portal';
        document.getElementById('googleClientLogin').hidden = portal !== 'client';
    }

    document.querySelectorAll('.portal-option').forEach(function (button) {
        button.addEventListener('click', function () {
            selectPortal(button, button.dataset.portal);
        });
    });

    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('loginPassword');
        const toggleBtn = document.getElementById('passwordToggle');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleBtn.textContent = 'Hide';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = 'Show';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Swal === 'undefined') {
            return;
        }

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Login Failed',
                text: {{ json_encode(session('error')) }},
                confirmButtonColor: '#198754'
            });
        @endif

        @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please check your input and try again.',
                confirmButtonColor: '#198754'
            });
        @endif

        @if(session('success') || session('status'))
            Swal.fire({
                icon: 'success',
                title: 'Welcome!',
                text: 'You have successfully logged in.',
                confirmButtonColor: '#198754'
            });
        @endif
    });
</script>
</body>
</html>