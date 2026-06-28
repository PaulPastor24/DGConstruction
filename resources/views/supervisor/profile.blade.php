@extends('layouts.supervisor')

@section('title', 'Supervisor Profile - Account Settings')
@section('page_title', 'Supervisor Profile')

@push('styles')
    <style>
        .profile-hero { padding: 1.15rem 1.2rem; }
        .profile-hero .avatar { width: 92px; height: 92px; border-radius: 22px; }
        .profile-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .profile-actions .btn { min-width: 150px; }
        .profile-field { display: grid; grid-template-columns: 1fr auto; gap: 1rem; align-items: center; padding: 0.9rem 0; border-bottom: 1px solid rgba(9,96,86,0.08); }
        .profile-field:last-child { border-bottom: none; }
        .profile-field-label { font-size: 0.72rem; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase; color: var(--supervisor-muted); }
        .profile-field-value { font-size: 0.95rem; font-weight: 700; color: var(--supervisor-primary); }
        .profile-card { border-radius: 18px; border: 1px solid rgba(9,96,86,0.08); background: #fff; }
        .profile-summary-box { border-radius: 16px; padding: 1rem; background: linear-gradient(135deg, #f9fdf8 0%, #ffffff 100%); border: 1px solid rgba(9,96,86,0.08); }
        .profile-summary-box .avatar { width: 72px; height: 72px; border-radius: 18px; }
        .profile-compact-list { display: flex; flex-direction: column; gap: 0.7rem; }
        .profile-compact-item { padding: 0.75rem 0.8rem; border-radius: 14px; background: #f9fdf8; border: 1px solid rgba(9,96,86,0.06); }
        .profile-accordion summary { list-style: none; cursor: pointer; }
        .profile-accordion summary::-webkit-details-marker { display: none; }
        @media (max-width: 1199px) {
            .profile-accordion summary { display: flex; align-items: center; justify-content: space-between; }
            .profile-accordion:not([open]) .profile-accordion-body { display: none; }
        }
        @media (max-width: 767px) {
            .profile-field { grid-template-columns: 1fr; }
            .profile-actions { width: 100%; }
            .profile-actions .btn { flex: 1 1 100%; }
        }
    </style>
@endpush

@section('content')
@php
    $lastLogin = optional($user->updated_at)->format('M j, Y') ?? 'Unknown';
    $employeeId = $user->user_id ?? 'N/A';
    $userPosition = ucfirst($user->role ?? 'Supervisor');
@endphp

<section class="page-card profile-hero mb-3">
    <div class="page-card-body d-flex flex-column flex-md-row justify-content-between gap-4 align-items-start">
        <div class="d-flex gap-3 align-items-center">
            <div class="avatar d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #2F6B3C, #66BB6A); color: #fff; font-size: 2rem; font-weight: 700;">{{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}</div>
            <div>
                <div class="eyebrow">Account Workspace</div>
                <h1 class="page-title mb-1">{{ $user->name ?? 'Supervisor' }}</h1>
                <p class="page-subtitle mb-0">Manage your workspace identity, access, and preferences without leaving the supervisor portal.</p>
            </div>
        </div>
        <div class="profile-actions">
            <a href="{{ route('profile.edit') }}" class="btn btn-primary-soft">Edit Profile</a>
            <a href="{{ route('supervisor.reports') }}" class="btn btn-outline-soft">Submit Report</a>
        </div>
    </div>
</section>

<div class="row g-3">
    <div class="col-12 col-xl-8">
        <section class="section-card mb-3">
            <div class="section-card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="fw-bold mb-1">Personal Information</h5>
                        <p class="text-muted small mb-0">Core identity details for the supervisor account.</p>
                    </div>
                </div>
                <div class="profile-card p-2">
                    <div class="profile-field px-3">
                        <div>
                            <div class="profile-field-label">Full name</div>
                            <div class="profile-field-value">{{ $user->name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="profile-field px-3">
                        <div>
                            <div class="profile-field-label">Email address</div>
                            <div class="profile-field-value">{{ $user->email ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-card mb-3">
            <div class="section-card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="fw-bold mb-1">Employment Information</h5>
                        <p class="text-muted small mb-0">Role, access, and account assignment details.</p>
                    </div>
                </div>
                <div class="profile-card p-2">
                    <div class="profile-field px-3">
                        <div>
                            <div class="profile-field-label">Position</div>
                            <div class="profile-field-value">{{ $userPosition }}</div>
                        </div>
                    </div>
                    <div class="profile-field px-3">
                        <div>
                            <div class="profile-field-label">Employee ID</div>
                            <div class="profile-field-value">{{ $employeeId }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-card">
            <div class="section-card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="fw-bold mb-1">Contact Information</h5>
                        <p class="text-muted small mb-0">Site and contact details used for operational coordination.</p>
                    </div>
                </div>
                <div class="profile-card p-2">
                    <div class="profile-field px-3">
                        <div>
                            <div class="profile-field-label">Contact number</div>
                            <div class="profile-field-value">{{ $user->contact_number ?? 'Not available' }}</div>
                        </div>
                    </div>
                    <div class="profile-field px-3">
                        <div>
                            <div class="profile-field-label">Location</div>
                            <div class="profile-field-value">{{ $user->address ?? 'Not available' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12 col-xl-4">
        <details class="section-card profile-accordion mb-3" open>
            <summary class="section-card-body">
                <span class="fw-bold">Security Settings</span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </summary>
            <div class="section-card-body profile-accordion-body">
                <div class="profile-compact-list">
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Account status</div>
                        <div class="fw-semibold">{{ $user->is_active ? 'Active' : 'Inactive' }}</div>
                    </div>
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Email verified</div>
                        <div class="fw-semibold">{{ $user->email_verified_at ? 'Yes' : 'No' }}</div>
                    </div>
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Password</div>
                        <div class="fw-semibold">••••••••</div>
                    </div>
                </div>
            </div>
        </details>

        <details class="section-card profile-accordion mb-3" open>
            <summary class="section-card-body">
                <span class="fw-bold">Notification Preferences</span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </summary>
            <div class="section-card-body profile-accordion-body">
                <div class="profile-compact-list">
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Report reminders</div>
                        <div class="fw-semibold">Enabled</div>
                    </div>
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Timeline alerts</div>
                        <div class="fw-semibold">Enabled</div>
                    </div>
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Project updates</div>
                        <div class="fw-semibold">Enabled</div>
                    </div>
                </div>
            </div>
        </details>

        <details class="section-card profile-accordion" open>
            <summary class="section-card-body">
                <span class="fw-bold">Recent Login Activity</span>
                <i class="bi bi-chevron-down small text-muted"></i>
            </summary>
            <div class="section-card-body profile-accordion-body">
                <div class="profile-compact-list">
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Last login</div>
                        <div class="fw-semibold">{{ $lastLogin }}</div>
                    </div>
                    <div class="profile-compact-item">
                        <div class="profile-field-label">Latest access</div>
                        <div class="fw-semibold">Supervisor portal access verified</div>
                    </div>
                </div>
            </div>
        </details>
    </div>
</div>
@endsection