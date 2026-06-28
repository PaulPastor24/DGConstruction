<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - D&G ConPhil</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    
    <link rel="stylesheet" href="{{ asset('css/landingpage.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}"> </head>
<body>

<div class="login-page-wrapper">
    <div class="login-card">
        <div class="login-form-section">
            <a href="{{ url('/') }}" class="modal-close" style="text-decoration: none; font-size: 1.25rem;">←</a>

            <div class="login-header">
                <div class="login-logo"><img src="{{ asset('images/image.png') }}" alt="D&G Construction logo"></div>
                <h2 class="login-title">D&G Construction</h2>
                <p class="login-subtitle">Project Management System</p>
            </div>

            <div class="role-tabs" id="roleTabs">
                <button type="button" class="role-tab active" onclick="selectRole(this, 'engineer')">Engineer</button>
                <button type="button" class="role-tab" onclick="selectRole(this, 'supervisor')">Supervisor</button>
                <button type="button" class="role-tab" onclick="selectRole(this, 'client')">Client</button>
            </div>

            @if ($errors->any())
                <div class="login-message error-banner">
                    {{ $errors->first('email') }}
                </div>
            @endif

            <form class="login-form" id="loginForm" action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <input type="email" name="email" class="form-input" id="loginEmail" placeholder="your@email.com" value="admin@dg-corp.ph" required>
                    <label class="form-label" for="loginEmail">Email Address</label>
                </div>

                <div class="form-group password-group">
                    <input type="password" name="password" class="form-input" id="loginPassword" placeholder="••••••••" value="password123" required>
                    <label class="form-label" for="loginPassword">Password</label>
                    <button type="button" class="password-toggle" id="passwordToggle" onclick="togglePasswordVisibility()">👁️</button>
                </div>

                <input type="hidden" id="selectedRole" name="role" value="engineer">

                <button type="submit" class="login-btn" id="loginBtn">Sign In</button>
            </form>

            <div class="demo-credentials">
                <strong>Demo Credentials:</strong><br>
                Engineer: admin@dg-corp.ph<br>
                Supervisor: supervisor@dg-corp.ph<br>
                Client: client@dg-corp.ph<br>
                Password: password123
            </div>
        </div>
    </div>
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
            toggleBtn.textContent = '👁️‍🗨️';
        } else {
            passwordInput.type = 'password';
            toggleBtn.textContent = '👁️';
        }
    }
</script>
</body>
</html>