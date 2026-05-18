@extends('layouts.admin')
@section('title', 'Manage Users — PodWave Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="pw-admin-page-title">Users</h1>
        <p class="pw-admin-page-sub">Manage all platform users</p>
    </div>
    <span class="text-muted small">{{ $users->total() }} total users</span>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('admin.users') }}" class="pw-filter-bar mb-4">
    <input type="text" name="search" value="{{ request('search') }}" class="form-control pw-filter-input" placeholder="Search by name or email…">
    <select name="role" class="form-select pw-filter-select" style="width:140px;">
        <option value="">All Roles</option>
        <option value="admin"    {{ request('role')=='admin'    ? 'selected':'' }}>Admin</option>
        <option value="creator"  {{ request('role')=='creator'  ? 'selected':'' }}>Creator</option>
        <option value="listener" {{ request('role')=='listener' ? 'selected':'' }}>Listener</option>
    </select>
    <select name="status" class="form-select pw-filter-select" style="width:130px;">
        <option value="">All Status</option>
        <option value="active" {{ request('status')=='active' ? 'selected':'' }}>Active</option>
        <option value="banned" {{ request('status')=='banned' ? 'selected':'' }}>Banned</option>
    </select>
    <button type="submit" class="btn pw-btn-primary btn-sm px-4">Filter</button>
    @if(request()->hasAny(['search','role','status']))
        <a href="{{ route('admin.users') }}" class="btn pw-btn-outline btn-sm px-4">Clear</a>
    @endif
</form>

<div class="pw-card p-0">
    <div class="table-responsive">
        <table class="pw-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Podcasts</th>
                    <th>Subscribers</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="rounded-circle" width="38" height="38" alt="">
                            <div>
                                <div class="text-white fw-semibold small">{{ $user->name }}</div>
                                <div class="text-muted" style="font-size:.75rem;">{{ $user->email }}</div>
                                @if($user->username)
                                    <div class="text-muted" style="font-size:.72rem;">@{{ $user->username }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td><span class="pw-badge-role pw-role-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                    <td><span class="text-white">{{ $user->podcasts_count }}</span></td>
                    <td><span class="text-white">{{ number_format($user->subscribers_count) }}</span></td>
                    <td><span class="text-muted small">{{ $user->created_at->format('M j, Y') }}</span></td>
                    <td>
                        @if($user->is_banned)
                            <span class="badge bg-danger">Banned</span>
                        @elseif($user->email_verified_at)
                            <span class="badge bg-success">Verified</span>
                        @else
                            <span class="badge bg-warning text-dark">Unverified</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.users.show', $user) }}" class="pw-action-btn pw-action-btn-primary">
                                <i class="bi bi-eye-fill"></i> View
                            </a>
                            @if(!$user->is_banned && !$user->isAdmin())
                                <button type="button" class="pw-action-btn pw-action-btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#banModal{{ $user->id }}">
                                    <i class="bi bi-slash-circle"></i> Ban
                                </button>
                            @elseif($user->is_banned)
                                <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="pw-action-btn pw-action-btn-success">
                                        <i class="bi bi-check-circle"></i> Unban
                                    </button>
                                </form>
                            @endif
                            @if(!$user->isAdmin())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="pw-action-btn pw-action-btn-danger"
                                    data-confirm="Permanently delete {{ $user->name }}? This cannot be undone.">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>

                {{-- Ban Modal --}}
                @if(!$user->is_banned && !$user->isAdmin())
                <div class="modal fade pw-modal" id="banModal{{ $user->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Ban {{ $user->name }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                                @csrf
                                <div class="modal-body py-4">
                                    <label class="pw-label">Reason for ban *</label>
                                    <textarea name="ban_reason" class="form-control pw-admin-input" rows="3"
                                        placeholder="Explain why this user is being banned…" required minlength="10"></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn pw-btn-outline btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger btn-sm px-4">Confirm Ban</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                @empty
                <tr>
                    <td colspan="7">
                        <div class="pw-empty-state">
                            <i class="bi bi-people"></i>
                            <p>No users found matching your filters.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($users->hasPages())
<div class="mt-4">{{ $users->withQueryString()->links() }}</div>
@endif
@endsection
