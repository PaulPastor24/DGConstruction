@extends('layouts.admin')

@section('title', 'Admin Profile - Account Settings')
@section('page_title', 'Admin Profile')

@push('styles')
    <style>
        :root {
            --profile-brand: #2a4028;
            --profile-brand-light: #eaf2eb;
            --profile-border: rgba(0, 0, 0, 0.08);
            --profile-text-muted: #6c757d;
        }

        .ui-card {
            background: #ffffff;
            border: 1px solid var(--profile-border);
            border-radius: 16px;
            padding: 1.5rem;
            height: 100%;
        }

        .ui-avatar-large {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
        }

        .ui-avatar-container {
            position: relative;
            display: inline-block;
        }

        .ui-avatar-edit-badge {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #fff;
            border: 1px solid var(--profile-border);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .badge-role {
            background-color: var(--profile-brand-light);
            color: var(--profile-brand);
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.75rem;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary-list li {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .summary-list li i {
            width: 24px;
            color: var(--profile-brand);
            font-size: 1rem;
        }

        .summary-list .label {
            width: 120px;
            color: var(--profile-text-muted);
        }

        .summary-list .value {
            font-weight: 500;
            color: #212529;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .info-row {
            background: #ffffff;
            border: 1px solid rgba(32, 52, 45, 0.08);
            border-radius: 16px;
            padding: 1.1rem 1.2rem;
            min-height: auto;
        }

        .info-row + .info-row {
            margin-top: 0.75rem;
        }

        .info-row strong {
            display: block;
            margin-bottom: 0.35rem;
            color: #4b6a58;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .info-item-value {
            font-size: 1rem;
            font-weight: 500;
            color: #2f4439;
        }

        .quick-link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            text-decoration: none;
            color: #212529;
            transition: background 0.2s;
        }

        .quick-link-item:last-child {
            border-bottom: none;
        }

        .quick-link-item i {
            color: var(--profile-brand);
            min-width: 24px;
        }

        .quick-link-item:hover {
            color: var(--profile-brand);
            background: rgba(42, 64, 40, 0.06);
        }

        .modal-content {
            border-radius: 20px;
            border: 1px solid rgba(42, 64, 40, 0.18);
            box-shadow: 0 24px 80px rgba(42, 64, 40, 0.12);
        }

        .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }

        .modal-title {
            color: #1f472c;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary-soft {
            background: linear-gradient(135deg, #2a4028, #4b6b46);
            color: #fff;
            border: none;
        }

        .btn-primary-soft:hover {
            background: linear-gradient(135deg, #1e3322, #3a5a38);
        }

        @media (max-width: 767px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
@php
    $firstName = $user->first_name ?? '';
    $lastName = $user->last_name ?? '';
    $showAddressField = \Illuminate\Support\Facades\Schema::hasColumn('users', 'address');
@endphp

<div class="container-fluid px-0">
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="ui-card">
                <h5 class="fw-bold mb-4">Profile Summary</h5>
                <div class="d-flex flex-column flex-sm-row gap-4 align-items-start">
                    <div class="ui-avatar-container">
                        <div class="ui-avatar-large d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #2a4028, #4b6b46); font-size: 2.5rem; font-weight: bold;">
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="fw-bold mb-1">{{ $user->name ?? 'Admin Name' }}</h4>
                        <span class="badge badge-role rounded-pill mb-3">Administrator</span>

                        <ul class="summary-list">
                            <li>
                                <i class="bi bi-card-text"></i>
                                <span class="label">Employee ID</span>
                                <span class="value">{{ $user->user_id ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <i class="bi bi-envelope"></i>
                                <span class="label">Email Address</span>
                                <span class="value">{{ $user->email ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <i class="bi bi-telephone"></i>
                                <span class="label">Phone Number</span>
                                <span class="value">{{ $user->contact_number ?? '+63 912 345 6789' }}</span>
                            </li>
                            <li>
                                <i class="bi bi-calendar-check"></i>
                                <span class="label">Member Since</span>
                                <span class="value">{{ optional($user->created_at)->format('M j, Y') ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="label">Account Status</span>
                                <span class="badge bg-success bg-opacity-10 text-success rounded px-2 py-1">{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="ui-card d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <div class="bg-success bg-opacity-10 p-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Account Security</h5>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold mb-1">Password</label>
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2">
                            <span class="text-muted tracking-wider">••••••••••••</span>
                            <button type="button" id="openPasswordModal" class="btn btn-sm btn-primary-soft rounded-3 px-3">
                                <i class="bi bi-key-fill me-1"></i> Change Password
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 px-3 py-3 rounded-3" style="background: rgba(42, 64, 40, 0.06);">
                        <div class="d-flex align-items-start gap-2">
                            <i class="bi bi-shield-lock-fill text-success fs-4"></i>
                            <div>
                                <h6 class="mb-1 fw-semibold" style="font-size: 0.95rem;">Security Status</h6>
                                <p class="text-muted small mb-0">Your account is protected. Change your password regularly and keep it private.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-2">
                    <label class="text-muted small fw-bold d-block mb-1">Last Login</label>
                    <div class="d-flex align-items-center justify-content-between text-muted small">
                        <div>
                            <span class="fw-semibold text-dark">{{ optional($user->updated_at)->format('M j, Y') ?? 'Unknown' }}</span>
                            <span class="mx-2">•</span>
                            <span>{{ optional($user->updated_at)->format('h:i A') ?? '00:00 AM' }}</span>
                        </div>
                        <div>
                            <i class="bi bi-laptop me-1"></i> System
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="ui-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person text-success fs-5"></i>
                        <div>
                            <h5 class="fw-bold mb-1">Personal Information</h5>
                            <p class="text-muted small mb-0">Manage your admin profile details.</p>
                        </div>
                    </div>
                    <button type="button" id="openProfileModal" class="btn btn-sm btn-primary-soft rounded-3 px-3">
                        <i class="bi bi-pencil-square me-1"></i> Edit Information
                    </button>
                </div>

                <div class="info-grid mb-4">
                    <div class="info-row">
                        <strong>First Name</strong>
                        <div class="info-item-value">{{ $user->first_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <strong>Last Name</strong>
                        <div class="info-item-value">{{ $user->last_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <strong>Email Address</strong>
                        <div class="info-item-value">{{ $user->email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <strong>Phone Number</strong>
                        <div class="info-item-value">{{ $user->contact_number ?? 'Not available' }}</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-12 col-xl-4 d-flex flex-column gap-4">
            <div class="ui-card text-center py-4">
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3 justify-content-start px-2">
                    <i class="bi bi-image text-muted fs-5"></i>
                    <h5 class="fw-bold mb-0">Profile Photo</h5>
                </div>
                <p class="text-muted small text-start px-2 mb-4">Update your profile picture</p>

                <div class="my-3">
                    <div class="ui-avatar-large mx-auto d-flex align-items-center justify-content-center text-white mb-3" style="background: linear-gradient(135deg, #2a4028, #4b6b46); font-size: 2.5rem; font-weight: bold; width: 100px; height: 100px;">
                        {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                    </div>
                </div>

                <div class="px-3">
                    <button type="button" class="btn btn-sm btn-outline-success w-100 rounded-3 py-2 fw-semibold mb-2">
                        <i class="bi bi-upload me-1"></i> Upload New Photo
                    </button>
                    <span class="text-muted d-block" style="font-size: 0.75rem;">JPG, PNG or GIF. Max size of 2MB.</span>
                    <span class="text-muted d-block" style="font-size: 0.75rem;">Recommended size: 400x400px</span>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-5 pt-3 border-top text-muted small">
        <p class="mb-1 mb-sm-0">&copy; {{ date('Y') }} D&G Construction Management System. All rights reserved.</p>
        <p class="mb-0">Version 1.0.0</p>
    </div>
</div>

<div class="modal fade" id="profileEditModal" tabindex="-1" aria-labelledby="profileEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="profileEditModalLabel">Edit Profile Information</h5>
                    <p class="text-muted small mb-0">Update your admin profile details.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profileUpdateForm" action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $firstName) }}" class="form-control @error('first_name') is-invalid @enderror" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $lastName) }}" class="form-control @error('last_name') is-invalid @enderror" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="contact_number" class="form-label">Phone Number</label>
                            <input type="text" name="contact_number" id="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="form-control @error('contact_number') is-invalid @enderror">
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if($showAddressField)
                        <div class="col-md-6">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="form-control @error('address') is-invalid @enderror">
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @endif
                    </div>
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-outline-secondary rounded-3 me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-soft rounded-3 px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
                    <p class="text-muted small mb-0">Enter your current password and choose a new one.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="passwordUpdateForm" action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-outline-secondary rounded-3 me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-soft rounded-3 px-4">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const profileModalEl = document.getElementById('profileEditModal');
        const passwordModalEl = document.getElementById('passwordModal');
        const profileModal = profileModalEl ? new bootstrap.Modal(profileModalEl) : null;
        const passwordModal = passwordModalEl ? new bootstrap.Modal(passwordModalEl) : null;

        const profileTrigger = document.getElementById('openProfileModal');
        const passwordTrigger = document.getElementById('openPasswordModal');

        if (profileTrigger && profileModal) {
            profileTrigger.addEventListener('click', function () {
                profileModal.show();
            });
        }

        if (passwordTrigger && passwordModal) {
            passwordTrigger.addEventListener('click', function () {
                passwordModal.show();
            });
        }

        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#2a4028',
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                confirmButtonColor: '#2a4028',
            });
        @endif

        @if($errors->any())
            const hasPasswordErrors = @json($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'));
            const hasProfileErrors = @json($errors->has('first_name') || $errors->has('last_name') || $errors->has('email') || $errors->has('contact_number') || $errors->has('address'));
            if (passwordModal && hasPasswordErrors) {
                passwordModal.show();
            } else if (profileModal && hasProfileErrors) {
                profileModal.show();
            }
        @endif
    });
</script>
@endpush
@endsection
