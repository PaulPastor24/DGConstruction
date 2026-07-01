<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - D&G Construction</title>
    
    <link rel="stylesheet" href="{{ asset('css/login.css') }}"> </head>
<body>

<div class="login-shell">
    <section class="login-hero">
        <a href="{{ url('/') }}" class="back-link" aria-label="Back to homepage">← Back</a>

        <div class="hero-badge">Design | Construction</div>
        <img class="hero-logo" src="{{ asset('images/image.png') }}" alt="D&G Construction logo">
        <h1>Welcome back.</h1>
        <p>
            Sign in to manage projects, supervise progress, and keep your construction workflow organized in one place.
        </p>

        <div class="hero-points">
            <span>Simple access</span>
            <span>Clear roles</span>
            <span>Fast dashboard login</span>
        </div>
    </section>

    <section class="login-panel">
        <div class="login-card">
            <div class="login-header">
                <p class="eyebrow">Secure sign in</p>
                <h2>D&G Construction</h2>
                <span>Project Management System</span>
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
                    <input type="email" name="email" class="form-input" id="loginEmail" placeholder="your@email.com" value="admin@dg-corp.ph" required>
                </div>

                <div class="form-group password-group">
                    <label class="form-label" for="loginPassword">Password</label>
                    <input type="password" name="password" class="form-input" id="loginPassword" placeholder="••••••••" value="password123" required>
                    <button type="button" class="password-toggle" id="passwordToggle" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">Show</button>
                </div>

                <input type="hidden" id="selectedRole" name="role" value="engineer">

                <button type="submit" class="login-btn" id="loginBtn">Sign In</button>
            </form>

            <div class="demo-credentials">
                <strong>Demo access</strong>
                <span>Engineer: admin@dg-corp.ph</span>
                <span>Supervisor: supervisor@dg-corp.ph</span>
                <span>Client: client@dg-corp.ph</span>
                <span>Password: password123</span>
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
        
        // Dynamically shifts demo accounts based on chosen enum values
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
</script>
</body>
</html>