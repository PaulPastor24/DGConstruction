@extends('layouts.admin')

@section('title', 'User Management')
@section('page_title', 'User Management')

@push('styles')
<!-- Import the Syne and Plus Jakarta Sans fonts if they aren't globally loaded -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Syne:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --ug-dark: var(--brand-dark, #212529);
        --ug-muted: #64748b;
        --ug-border: var(--border, #dee2e6);
        --ug-background: var(--bg-page, #f8f9fa);
        --ug-white: var(--surface, #ffffff);
        --ug-accent: var(--brand-green, #198754);
        --ug-accent-soft: var(--brand-accent-soft, #e8f5e9);
        --ug-accent-hover: var(--brand-green, #198754);
    }

    .ug-page {
        width: 100%;
        padding: 4px 0 28px;
    }

    /* 100% Exact typography match to your inventory stylesheet rule */
    .mi-page.inventory-green-theme .dashboard-title-area h2,
    .mi-page.inventory-green-theme .dashboard-title-area h2 * {
        font-family: 'Syne', 'Plus Jakarta Sans', 'Helvetica Neue', Arial, sans-serif !important;
        font-size: 28px !important;
        font-weight: 600 !important;
        color: #111827 !important;
        letter-spacing: -0.02em !important;
        margin-bottom: 6px !important;
    }

    .user-card-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .user-card-icon.accent {
        background-color: var(--ug-accent-soft);
        color: var(--ug-accent);
    }
    .user-card-icon.warning {
        background-color: #fff3cd;
        color: #ffc107;
    }
    .avatar-circle-ui {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        font-size: 13px;
        background: linear-gradient(135deg, #6c757d, #495057);
    }
    .custom-admin-table th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.3px;
        text-transform: uppercase;
        border-bottom-width: 1px;
        padding-top: 14px;
        padding-bottom: 14px;
    }
    .btn-icon-only {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .pulse-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .px-2\.5 {
        padding-left: 0.65rem !important;
        padding-right: 0.65rem !important;
    }

    #editUserModal .form-control:focus,
    #editUserModal .form-select:focus,
    #addUserModal .form-control:focus,
    #addUserModal .form-select:focus {
        border-color: var(--ug-accent);
        box-shadow: 0 0 0 3px var(--ug-accent-soft);
    }
    #editUserModal .form-label,
    #addUserModal .form-label {
        margin-bottom: 0.35rem;
    }
    #addUserModal .invalid-feedback.d-block {
        display: block !important;
    }
</style>
@endpush

@section('content')
<!-- Matched parent CSS scoping selectors -->
<div class="ug-page mi-page inventory-green-theme">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Adjusted to target the exact title typography block layout structure -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="dashboard-title-area">
            <h2>User Management</h2>
            <p class="text-muted small mb-0">Manage system users, roles, and account statuses from a single panel.</p>
        </div>
        <div>
            <button type="button"
                    class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm"
                    style="background-color: var(--ug-accent) !important; border-color: var(--ug-accent) !important;"
                    data-bs-toggle="modal"
                    data-bs-target="#addUserModal">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add New User</span>
            </button>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="user-card-icon accent">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Users</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $total_users ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">All accounts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="user-card-icon accent">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Active Users</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $active_users_count ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Currently enabled</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="user-card-icon warning">
                        <i class="bi bi-person-dash"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Inactive Users</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $inactive_users_count ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Login disabled</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="user-card-icon accent">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Clients</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $clients_count ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Client accounts</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="user-card-icon accent">
                        <i class="bi bi-cone-striped"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Engineers</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $engineers_count ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Engineering staff</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="user-card-icon accent">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Supervisors</div>
                        <div class="fs-2 fw-bold text-dark lh-1 my-1">{{ $supervisors_count ?? 0 }}</div>
                        <div class="text-muted" style="font-size: 11px;">Field supervision</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form id="filterForm" method="GET" action="{{ route('admin.users.index') }}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5 position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="search" id="searchInput" class="form-control border-start-0 ps-1" placeholder="Search by name, email, or company name..." value="{{ request('search') }}" autocomplete="off">
                            @if(request('search'))
                                <button type="button" class="btn btn-link text-muted position-absolute end-0 top-50 translate-middle-y z-3 border-0 py-0 pe-3" onclick="clearSearchField()" style="box-shadow: none;">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3">
                        <select name="role" id="roleFilter" class="form-select text-muted">
                            <option value="">All Roles</option>
                            <option value="engineer" {{ request('role') == 'engineer' ? 'selected' : '' }}>Engineer/Administrator</option>
                            <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Site Supervisor</option>
                            <option value="client" {{ request('role') == 'client' ? 'selected' : '' }}>Client</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="status" id="statusFilter" class="form-select text-muted">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="per_page" id="perPageFilter" class="form-select text-muted">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 rows</option>
                            <option value="25" {{ request('per_page', '25') == '25' ? 'selected' : '' }}>25 rows</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 rows</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 rows</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 custom-admin-table">
                <thead>
                    <tr>
                        <th width="60" class="ps-4">#</th>
                        <th>User Account</th>
                        <th>Contact Number</th>
                        <th>Assigned Role</th>
                        <th>System Access</th>
                        <th width="120" class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td class="ps-4 text-muted small fw-semibold">
                                {{ ($users->currentPage() - 1) * $users->perPage() + $index + 1 }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle-ui d-flex align-items-center justify-content-center fw-bold text-white uppercase shadow-sm">
                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark lh-base">{{ $user->first_name }} {{ $user->last_name }}</div>
                                        <div class="text-muted small d-flex align-items-center gap-1">
                                            <i class="bi bi-envelope-open" style="font-size:11px;"></i>
                                            <span>{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->contact_number)
                                    <div class="fw-semibold text-secondary small d-flex align-items-center gap-1">
                                        <i class="bi bi-telephone" style="font-size:11px;"></i>
                                        <span>{{ $user->contact_number }}</span>
                                    </div>
                                @else
                                    <div class="text-muted small">Not provided</div>
                                @endif
                            </td>
                            <td>
                                @php
                                    $roleLabel = match(strtolower($user->role)) {
                                        'engineer' => 'Engineer/Administrator',
                                        'supervisor' => 'Site Supervisor',
                                        'client' => 'Client',
                                        default => ucfirst($user->role)
                                    };
                                @endphp
                                <span class="fw-semibold text-dark small">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td>
                                @if($user->is_active == 1)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                        <span class="pulse-indicator bg-success"></span> Active
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                        <span class="pulse-indicator bg-warning"></span> Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-1">
                                    <button type="button"
                                            class="btn btn-sm btn-icon-only border rounded-3 bg-white shadow-sm text-secondary btn-edit-user"
                                            title="Edit Profile"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editUserModal"
                                            data-user-id="{{ $user->user_id }}"
                                            data-first-name="{{ $user->first_name }}"
                                            data-last-name="{{ $user->last_name }}"
                                            data-email="{{ $user->email }}"
                                            data-contact="{{ $user->contact_number }}"
                                            data-role="{{ $user->role }}"
                                            data-active="{{ $user->is_active ? 1 : 0 }}"
                                            data-update-url="{{ route('admin.users.update', $user) }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-icon-only border rounded-3 bg-white shadow-sm text-danger" title="Delete User Account" onclick="confirmUserDeletion('{{ $user->user_id }}', '{{ $user->first_name }} {{ $user->last_name }}')">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->user_id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="mb-2 fs-2 text-secondary-subtle"><i class="bi bi-person-exclamation"></i></div>
                                <h6 class="fw-bold mb-1">No matches found</h6>
                                <p class="small mb-0 text-secondary-50">Try broadening your parameters or query strings.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages() || $users->total() > 0)
            <div class="card-footer bg-white border-top border-light-subtle d-flex flex-wrap align-items-center justify-content-between p-3 gap-2">
                <div class="text-muted small fw-semibold">
                    Showing <span class="text-dark">{{ $users->firstItem() ?? 0 }}</span> to <span class="text-dark">{{ $users->lastItem() ?? 0 }}</span> of <span class="text-dark">{{ $users->total() }}</span> recorded entries
                </div>
                <div class="pagination-layout-wrapper">
                    {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 px-4 py-3" style="background: var(--ug-accent-soft);">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0" id="editUserModalLabel" style="color:#111827;">
                        <i class="bi bi-person-gear" style="color: var(--ug-accent);"></i>
                        Edit User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editUserForm" novalidate>
                    <div class="modal-body px-4 py-4">
                        <input type="hidden" id="edit_user_id" name="user_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_first_name" class="form-label fw-semibold small text-secondary">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name"
                                       minlength="2" maxlength="100" autocomplete="given-name" required>
                                <div class="invalid-feedback" data-error-for="first_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_last_name" class="form-label fw-semibold small text-secondary">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name"
                                       minlength="2" maxlength="100" autocomplete="family-name" required>
                                <div class="invalid-feedback" data-error-for="last_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_email" class="form-label fw-semibold small text-secondary">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" maxlength="150" autocomplete="email" required>
                                <div class="invalid-feedback" data-error-for="email"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_contact_number" class="form-label fw-semibold small text-secondary">Contact Number</label>
                                <input type="tel" class="form-control" id="edit_contact_number" name="contact_number"
                                       placeholder="e.g. 09171234567" maxlength="20" inputmode="numeric" autocomplete="tel">
                                <div class="invalid-feedback" data-error-for="contact_number"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_password" class="form-label fw-semibold small text-secondary">New Password</label>
                                <input type="password" class="form-control" id="edit_password" name="password" autocomplete="new-password">
                                <div class="invalid-feedback" data-error-for="password"></div>
                                <small class="text-muted">Leave blank to keep current. Min. 8 chars, mixed case, number &amp; symbol.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_password_confirmation" class="form-label fw-semibold small text-secondary">Confirm New Password</label>
                                <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation" autocomplete="new-password">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_role" class="form-label fw-semibold small text-secondary">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_role" name="role" required>
                                    <option value="">-- Select Role --</option>
                                    <option value="engineer">Engineer/Administrator</option>
                                    <option value="supervisor">Site Supervisor</option>
                                    <option value="client">Client</option>
                                </select>
                                <div class="invalid-feedback" data-error-for="role"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_is_active" class="form-label fw-semibold small text-secondary">Status</label>
                                <select class="form-select" id="edit_is_active" name="is_active">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <div class="invalid-feedback" data-error-for="is_active"></div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn rounded-3 px-4 text-white" id="editUserSubmitBtn" style="background-color: var(--ug-accent); border-color: var(--ug-accent);">
                            <i class="bi bi-save"></i> <span id="editUserSubmitText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                <div class="modal-header border-0 px-4 py-3" style="background: var(--ug-accent-soft);">
                    <h5 class="modal-title fw-bold d-flex align-items-center gap-2 mb-0" id="addUserModalLabel" style="color:#111827;">
                        <i class="bi bi-person-plus-fill" style="color: var(--ug-accent);"></i>
                        Add New User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="addUserForm" novalidate>
                    <input type="hidden" name="idempotency_key" id="add_idempotency_key">
                    <div class="modal-body px-4 py-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="add_first_name" class="form-label fw-semibold small text-secondary">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_first_name" name="first_name"
                                       placeholder="e.g. Juan" minlength="2" maxlength="100"
                                       autocomplete="given-name" required>
                                <div class="invalid-feedback" data-error-for="first_name"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="add_last_name" class="form-label fw-semibold small text-secondary">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="add_last_name" name="last_name"
                                       placeholder="e.g. Dela Cruz" minlength="2" maxlength="100"
                                       autocomplete="family-name" required>
                                <div class="invalid-feedback" data-error-for="last_name"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="add_email" class="form-label fw-semibold small text-secondary">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="add_email" name="email"
                                       placeholder="name@example.com" maxlength="150"
                                       autocomplete="email" required>
                                <div class="invalid-feedback" data-error-for="email"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="add_contact_number" class="form-label fw-semibold small text-secondary">Contact Number</label>
                                <input type="tel" class="form-control" id="add_contact_number" name="contact_number"
                                       placeholder="e.g. 09171234567" maxlength="20"
                                       inputmode="numeric" autocomplete="tel">
                                <div class="invalid-feedback" data-error-for="contact_number"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="add_password" class="form-label fw-semibold small text-secondary">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="add_password" name="password" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary" type="button" tabindex="-1" onclick="togglePasswordVisibility('add_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback d-block text-danger small" data-error-for="password"></div>
                                <small class="text-muted">Min. 8 characters, with uppercase, lowercase, a number, and a symbol</small>
                            </div>
                            <div class="col-md-6">
                                <label for="add_password_confirmation" class="form-label fw-semibold small text-secondary">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="add_password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary" type="button" tabindex="-1" onclick="togglePasswordVisibility('add_password_confirmation', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="add_role" class="form-label fw-semibold small text-secondary">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="add_role" name="role" required>
                                    <option value="">-- Select Role --</option>
                                    <option value="engineer">Engineer/Administrator</option>
                                    <option value="supervisor">Site Supervisor</option>
                                    <option value="client">Client</option>
                                </select>
                                <div class="invalid-feedback" data-error-for="role"></div>
                            </div>
                        </div>

                        <div class="alert alert-secondary border-0 bg-light rounded-3 mt-3 mb-0 d-flex gap-2 py-2 px-3">
                            <i class="bi bi-info-circle text-secondary mt-1"></i>
                            <div class="small text-muted mb-0">
                                <strong>Engineer/Administrator</strong> — full system access.
                                <strong>Site Supervisor</strong> — manages assigned projects.
                                <strong>Client</strong> — read-only project visibility.
                                New accounts are created as <strong>Active</strong> by default.
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn rounded-3 px-4 text-white" id="addUserSubmitBtn" style="background-color: var(--ug-accent); border-color: var(--ug-accent);">
                            <i class="bi bi-save"></i> <span id="addUserSubmitText">Create User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const filterForm = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
        const selectFilters = [
            document.getElementById('roleFilter'),
            document.getElementById('statusFilter'),
            document.getElementById('perPageFilter')
        ];

        // Dropdown filters submit instantly on change
        selectFilters.forEach(filter => {
            if (filter) {
                filter.addEventListener('change', function() {
                    filterForm.submit();
                });
            }
        });

        if (searchInput) {
            // Keep cursor beautifully active at the end of the line on filter/search reloads
            if (searchInput.value !== "") {
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
                searchInput.focus();
            }

            // Clean Automatic search using a smart typing delay (Debounce)
            let typingTimer;
            const doneTypingInterval = 600; // Waits 600ms after you stop typing to execute search automatically

            searchInput.addEventListener('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(function() {
                    filterForm.submit();
                }, doneTypingInterval);
            });

            // Prevent hitting Enter manually from clashing with the timer script
            searchInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(typingTimer);
                    filterForm.submit();
                }
            });
        }
    });

    function clearSearchField() {
        const input = document.getElementById('searchInput');
        if (input) input.value = '';
        document.getElementById('filterForm').submit();
    }

    function confirmUserDeletion(userId, fullName) {
        Swal.fire({
            title: 'Delete user record?',
            text: `You are about to remove ${fullName}. This action cannot be reversed!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete account',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const form = document.getElementById(`delete-form-${userId}`);
            if (!form) return;

            const url = form.getAttribute('action');
            const formData = new FormData(form); // already carries _token and _method=DELETE

            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async (response) => {
                if (response.status === 419) {
                    Swal.fire({
                        title: 'Session Expired',
                        text: 'Your session has expired. Please refresh the page and try again.',
                        icon: 'warning',
                        confirmButtonColor: '#198754'
                    });
                    return;
                }

                const data = await response.json().catch(() => null);

                if (!response.ok) {
                    Swal.fire('Error', (data && data.message) || 'Failed to delete user.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Deleted!',
                    text: (data && data.message) || 'User deleted successfully!',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then((res) => {
                    if (res.isConfirmed) {
                        window.location.reload();
                    }
                });
            })
            .catch(() => {
                Swal.fire('Error', 'A network error occurred. Please try again.', 'error');
            });
        });
    }

document.addEventListener('DOMContentLoaded', function () {
    const editUserModalEl = document.getElementById('editUserModal');
    const editUserForm = document.getElementById('editUserForm');
    const submitBtn = document.getElementById('editUserSubmitBtn');
    const submitText = document.getElementById('editUserSubmitText');
    let currentUpdateUrl = null;

    // Helper to clear existing error styling/messages
    function clearEditErrors() {
        editUserForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        editUserForm.querySelectorAll('[data-error-for]').forEach(el => el.textContent = '');
    }

    // Populate the modal whenever an edit button is clicked
    document.querySelectorAll('.btn-edit-user').forEach(function (btn) {
        btn.addEventListener('click', function () {
            clearEditErrors();
            currentUpdateUrl = btn.dataset.updateUrl;

            document.getElementById('edit_user_id').value = btn.dataset.userId;
            document.getElementById('edit_first_name').value = btn.dataset.firstName || '';
            document.getElementById('edit_last_name').value = btn.dataset.lastName || '';
            document.getElementById('edit_email').value = btn.dataset.email || '';
            document.getElementById('edit_contact_number').value = btn.dataset.contact || '';
            document.getElementById('edit_role').value = btn.dataset.role || '';
            document.getElementById('edit_is_active').value = (btn.dataset.active === '1') ? '1' : '0';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
        });
    });

    // Reset form state whenever the modal is closed normally
    editUserModalEl.addEventListener('hidden.bs.modal', function () {
        editUserForm.reset();
        clearEditErrors();
    });

    // Handle and display validation errors inside the modal
    function showValidationErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const input = editUserForm.querySelector(`[name="${field}"]`);
            const errorBox = editUserForm.querySelector(`[data-error-for="${field}"]`);
            if (input) input.classList.add('is-invalid');
            if (errorBox) errorBox.textContent = errors[field][0];
        });

        const messages = Object.values(errors).map(arr => arr[0]);
        Swal.fire({
            title: 'Please check the form',
            html: '<ul class="text-start mb-0 ps-3">' + messages.map(m => `<li>${m}</li>`).join('') + '</ul>',
            icon: 'warning',
            confirmButtonColor: '#198754'
        });
    }

    // Form Submission logic
    editUserForm.addEventListener('submit', function (e) {
        e.preventDefault();
        clearEditErrors();

        if (!currentUpdateUrl) return;

        // Client-side validation mirroring UpdateUserRequest's server rules.
        const NAME_REGEX = /^[\p{L}\s'\-.]{2,100}$/u;
        const PH_MOBILE_REGEX = /^(?:\+63|0)9\d{9}$/;

        const firstName = document.getElementById('edit_first_name').value.trim();
        const lastName = document.getElementById('edit_last_name').value.trim();
        const email = document.getElementById('edit_email').value.trim();
        const contactNumber = document.getElementById('edit_contact_number').value.trim();
        const role = document.getElementById('edit_role').value;
        const password = document.getElementById('edit_password').value;
        const passwordConfirmation = document.getElementById('edit_password_confirmation').value;

        const errors = {};

        if (!firstName) {
            errors.first_name = ['First name is required.'];
        } else if (!NAME_REGEX.test(firstName)) {
            errors.first_name = ['First name may only contain letters, spaces, hyphens, apostrophes, and periods (min. 2 characters).'];
        }

        if (!lastName) {
            errors.last_name = ['Last name is required.'];
        } else if (!NAME_REGEX.test(lastName)) {
            errors.last_name = ['Last name may only contain letters, spaces, hyphens, apostrophes, and periods (min. 2 characters).'];
        }

        if (!email) {
            errors.email = ['Email address is required.'];
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.email = ['Please provide a valid email address.'];
        }

        if (contactNumber && !PH_MOBILE_REGEX.test(contactNumber.replace(/[\s\-]/g, ''))) {
            errors.contact_number = ['Enter a valid Philippine mobile number, e.g. 09171234567 or +639171234567.'];
        }

        if (!role) {
            errors.role = ['Please select a role.'];
        }

        // Password is optional on edit — only validate it if the person is
        // actually setting a new one.
        if (password || passwordConfirmation) {
            if (password.length < 8) {
                errors.password = ['Password must be at least 8 characters.'];
            } else if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/\d/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
                errors.password = ['Password must include an uppercase letter, a lowercase letter, a number, and a symbol.'];
            } else if (password !== passwordConfirmation) {
                errors.password = ['Password confirmation does not match.'];
            }
        }

        if (Object.keys(errors).length > 0) {
            showValidationErrors(errors);
            return;
        }

        submitBtn.disabled = true;
        submitText.textContent = 'Saving...';

        const formData = new FormData(editUserForm);
        formData.append('_method', 'PUT');

        fetch(currentUpdateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async (response) => {
            if (response.status === 419) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. Please refresh the page and try again.',
                    icon: 'warning',
                    confirmButtonColor: '#198754'
                });
                return;
            }

            const data = await response.json().catch(() => null);

            if (response.status === 422 && data && data.errors) {
                showValidationErrors(data.errors);
                return;
            }

            if (response.status === 403) {
                Swal.fire('Not Allowed', (data && data.message) || 'You are not authorized to perform this action.', 'error');
                return;
            }

            if (!response.ok) {
                Swal.fire('Error', (data && data.message) || 'Something went wrong. Please try again.', 'error');
                return;
            }

            if (data && data.info) {
                Swal.fire('No Changes', data.message, 'info');
                return;
            }

            /* ====================================================================
             * FIX IMPLEMENTATION: Hide Modal First, Then Show SweetAlert Over Table
             * ==================================================================== */
            const modalInstance = bootstrap.Modal.getInstance(editUserModalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            // Listen for the exact moment the modal and its backdrop have fully faded out
            editUserModalEl.addEventListener('hidden.bs.modal', function onModalClose() {
                // Instantly remove the listener so it doesn't duplicate on future actions
                editUserModalEl.removeEventListener('hidden.bs.modal', onModalClose);

                // Fire SweetAlert safely over the clean table view
                Swal.fire({
                    title: 'Updated!',
                    text: (data && data.message) || 'User updated successfully!',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reload the data view only after clicking "OK"
                        window.location.reload();
                    }
                });
            }, { once: true });
        })
        .catch(() => {
            Swal.fire('Error', 'A network error occurred. Please try again.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitText.textContent = 'Save Changes';
        });
    });
});

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const addUserModalEl = document.getElementById('addUserModal');
    const addUserForm = document.getElementById('addUserForm');
    const addSubmitBtn = document.getElementById('addUserSubmitBtn');
    const addSubmitText = document.getElementById('addUserSubmitText');
    const addUserStoreUrl = "{{ route('admin.users.store') }}";

    if (!addUserModalEl || !addUserForm) return;

    // Helper to clear existing error styling/messages
    function clearAddErrors() {
        addUserForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        addUserForm.querySelectorAll('[data-error-for]').forEach(el => el.textContent = '');
    }

    // Reset the form completely whenever the modal is opened
    addUserModalEl.addEventListener('show.bs.modal', function () {
        addUserForm.reset();
        clearAddErrors();
        // Fresh key per modal-open. Sent with the request so the server can
        // recognize and collapse duplicate submissions of this same attempt,
        // regardless of what causes the duplicate on the client.
        document.getElementById('add_idempotency_key').value =
            (window.crypto && crypto.randomUUID) ? crypto.randomUUID()
                : `${Date.now()}-${Math.random().toString(36).slice(2)}`;
    });

    // Reset form state whenever the modal is closed
    addUserModalEl.addEventListener('hidden.bs.modal', function () {
        addUserForm.reset();
        clearAddErrors();
    });

    // Handle and display validation errors inside the modal
    function showAddValidationErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const input = addUserForm.querySelector(`[name="${field}"]`);
            const errorBox = addUserForm.querySelector(`[data-error-for="${field}"]`);
            if (input) input.classList.add('is-invalid');
            if (errorBox) errorBox.textContent = errors[field][0];
        });

        const messages = Object.values(errors).map(arr => arr[0]);
        Swal.fire({
            title: 'Please check the form',
            html: '<ul class="text-start mb-0 ps-3">' + messages.map(m => `<li>${m}</li>`).join('') + '</ul>',
            icon: 'warning',
            confirmButtonColor: '#198754'
        });
    }

    let isAddingUser = false; // hard re-entrancy guard, independent of button.disabled

    addUserForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // If a submission is already in flight, ignore this event entirely.
        // This is what actually stops a double-fired submit event from
        // sending a second request (button.disabled alone can lag behind
        // a fast double-click/double-tap on some browsers).
        if (isAddingUser) {
            return;
        }

        clearAddErrors();

        // Client-side validation mirroring the server rules in
        // StoreUserRequest, so the person gets instant feedback without a
        // round trip. The server remains the source of truth — these
        // checks are for UX only and are re-verified on the backend.
        const NAME_REGEX = /^[\p{L}\s'\-.]{2,100}$/u;
        const PH_MOBILE_REGEX = /^(?:\+63|0)9\d{9}$/;

        const firstName = document.getElementById('add_first_name').value.trim();
        const lastName = document.getElementById('add_last_name').value.trim();
        const email = document.getElementById('add_email').value.trim();
        const contactNumber = document.getElementById('add_contact_number').value.trim();
        const role = document.getElementById('add_role').value;
        const password = document.getElementById('add_password').value;
        const passwordConfirmation = document.getElementById('add_password_confirmation').value;

        const errors = {};

        if (!firstName) {
            errors.first_name = ['First name is required.'];
        } else if (!NAME_REGEX.test(firstName)) {
            errors.first_name = ['First name may only contain letters, spaces, hyphens, apostrophes, and periods (min. 2 characters).'];
        }

        if (!lastName) {
            errors.last_name = ['Last name is required.'];
        } else if (!NAME_REGEX.test(lastName)) {
            errors.last_name = ['Last name may only contain letters, spaces, hyphens, apostrophes, and periods (min. 2 characters).'];
        }

        if (!email) {
            errors.email = ['Email address is required.'];
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.email = ['Please provide a valid email address.'];
        }

        if (contactNumber && !PH_MOBILE_REGEX.test(contactNumber.replace(/[\s\-]/g, ''))) {
            errors.contact_number = ['Enter a valid Philippine mobile number, e.g. 09171234567 or +639171234567.'];
        }

        if (!role) {
            errors.role = ['Please select a role.'];
        }

        if (!password) {
            errors.password = ['Password is required.'];
        } else if (password.length < 8) {
            errors.password = ['Password must be at least 8 characters.'];
        } else if (!/[a-z]/.test(password) || !/[A-Z]/.test(password) || !/\d/.test(password) || !/[^A-Za-z0-9]/.test(password)) {
            errors.password = ['Password must include an uppercase letter, a lowercase letter, a number, and a symbol.'];
        } else if (password !== passwordConfirmation) {
            errors.password = ['Password confirmation does not match.'];
        }

        if (Object.keys(errors).length > 0) {
            showAddValidationErrors(errors);
            return;
        }

        isAddingUser = true;
        addSubmitBtn.disabled = true;
        addSubmitText.textContent = 'Creating...';

        const formData = new FormData(addUserForm);

        fetch(addUserStoreUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(async (response) => {
            if (response.status === 419) {
                Swal.fire({
                    title: 'Session Expired',
                    text: 'Your session has expired. Please refresh the page and try again.',
                    icon: 'warning',
                    confirmButtonColor: '#198754'
                });
                return;
            }

            const data = await response.json().catch(() => null);

            if (response.status === 422 && data && data.errors) {
                showAddValidationErrors(data.errors);
                return;
            }

            if (response.status === 403) {
                Swal.fire('Not Allowed', (data && data.message) || 'You are not authorized to perform this action.', 'error');
                return;
            }

            if (!response.ok) {
                Swal.fire('Error', (data && data.message) || 'Something went wrong. Please try again.', 'error');
                return;
            }

            // Hide modal first, then show SweetAlert cleanly over the table
            const modalInstance = bootstrap.Modal.getInstance(addUserModalEl);
            if (modalInstance) {
                modalInstance.hide();
            }

            addUserModalEl.addEventListener('hidden.bs.modal', function onModalClose() {
                addUserModalEl.removeEventListener('hidden.bs.modal', onModalClose);

                Swal.fire({
                    title: 'User Created!',
                    text: (data && data.message) || 'User created successfully!',
                    icon: 'success',
                    confirmButtonColor: '#198754',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
            }, { once: true });
        })
        .catch(() => {
            Swal.fire('Error', 'A network error occurred. Please try again.', 'error');
        })
        .finally(() => {
            isAddingUser = false;
            addSubmitBtn.disabled = false;
            addSubmitText.textContent = 'Create User';
        });
    });
});
</script>
@endpush