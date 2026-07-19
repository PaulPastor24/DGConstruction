<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - D&G Construction</title>
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
                <img class="hero-logo-left" src="{{ asset('images/image.png') }}" alt="D&G Construction logo">
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
            <div class="login-header">
                <h2>Portal Access</h2>
                <span class="subtitle">D&G Construction Management Hub</span>
            </div>

            <div class="role-tabs" id="roleTabs">
                <button type="button" class="role-tab active" onclick="selectRole(this, 'engineer')">Engineer</button>
                <button type="button" class="role-tab" onclick="selectRole(this, 'supervisor')">Supervisor</button>
                <button type="button" class="role-tab" onclick="selectRole(this, 'client')">Client</button>
            </div>

            @if (session('error'))
                <div class="login-message error-banner">
                    {{ session('error') }}
                </div>
            @endif

            <form class="login-form" id="loginForm" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="loginEmail">Email Address</label>
                    <input type="email" name="email" class="form-input" id="loginEmail" placeholder="name@company.com" value="admin@dg-corp.ph" required>
                </div>

                <div class="form-group password-group">
                    <div class="label-row">
                        <label class="form-label" for="loginPassword">Password</label>
                    </div>
                    <div class="input-container">
                        <input type="password" name="password" class="form-input" id="loginPassword" placeholder="••••••••" value="password123" required>
                        <button type="button" class="password-toggle" id="passwordToggle" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">Show</button>
                    </div>
                </div>

                <input type="hidden" id="selectedRole" name="role" value="engineer">

                <button type="submit" class="login-btn" id="loginBtn">
                    Sign In to Dashboard
                </button>
            </form>

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
        </div>
    </section>
</div>

<script>
    let currentRole = 'engineer';

    function selectRole(el, role) {
        document.querySelectorAll('.role-tab').forEach(tab => tab.classList.remove('active'));
        el.classList.add('active');
        currentRole = role;
        document.getElementById('selectedRole').value = role;
        
        const emailInput = document.getElementById('loginEmail');
        if(role === 'engineer') emailInput.value = 'admin@dg-corp.ph';
        if(role === 'supervisor') emailInput.value = 'supervisor@dg-corp.ph';
        if(role === 'client') emailInput.value = 'client@dg-corp.ph';
    }

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

        @if($errors->any())
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