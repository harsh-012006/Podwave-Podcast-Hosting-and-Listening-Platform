@extends('layouts.admin')
@section('title', 'Categories — PodWave Admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="pw-admin-page-title">Categories</h1>
        <p class="pw-admin-page-sub">Manage podcast categories shown to users</p>
    </div>
    <button class="btn pw-btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i class="bi bi-plus-lg me-2"></i>Add Category
    </button>
</div>

<div class="pw-card p-0">
    <table class="pw-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Icon</th>
                <th>Color</th>
                <th>Podcasts</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $cat)
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:36px;height:36px;border-radius:8px;background:{{ $cat->color }}22;display:flex;align-items:center;justify-content:center;color:{{ $cat->color }};">
                            <i class="bi {{ $cat->icon }}"></i>
                        </div>
                        <div class="text-white fw-semibold small">{{ $cat->name }}</div>
                    </div>
                </td>
                <td><code class="text-muted small">{{ $cat->icon }}</code></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:18px;height:18px;border-radius:4px;background:{{ $cat->color }};"></div>
                        <code class="text-muted small">{{ $cat->color }}</code>
                    </div>
                </td>
                <td><span class="text-white">{{ $cat->podcasts_count }}</span></td>
                <td>
                    <span class="badge {{ $cat->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $cat->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="pw-action-btn pw-action-btn-primary"
                            data-bs-toggle="modal" data-bs-target="#editCat{{ $cat->id }}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="pw-action-btn pw-action-btn-danger border-0"
                                data-confirm="Delete '{{ $cat->name }}'? Podcasts in this category will be uncategorized.">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>

            {{-- Edit Modal --}}
            <div class="modal fade pw-modal" id="editCat{{ $cat->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit: {{ $cat->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="{{ route('admin.categories.update', $cat) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="modal-body py-4">
                                <div class="pw-admin-form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" value="{{ $cat->name }}" class="form-control pw-admin-input" required>
                                </div>
                                <div class="row g-3">
                                    <div class="col-8">
                                        <label class="d-block text-muted small mb-1">Icon class (Bootstrap Icons)</label>
                                        <input type="text" name="icon" value="{{ $cat->icon }}" class="form-control pw-admin-input" placeholder="bi-mic-fill">
                                    </div>
                                    <div class="col-4">
                                        <label class="d-block text-muted small mb-1">Color</label>
                                        <input type="color" name="color" value="{{ $cat->color }}" class="form-control pw-admin-input p-1" style="height:44px;">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_active" id="active{{ $cat->id }}" class="form-check-input" value="1" {{ $cat->is_active ? 'checked' : '' }}>
                                        <label for="active{{ $cat->id }}" class="form-check-label text-muted small">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn pw-btn-outline btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn pw-btn-primary btn-sm px-4">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <tr>
                <td colspan="6"><div class="pw-empty-state"><i class="bi bi-grid"></i><p>No categories yet.</p></div></td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Add Category Modal --}}
<div class="modal fade pw-modal" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="pw-admin-form-group">
                        <label>Category Name *</label>
                        <input type="text" name="name" class="form-control pw-admin-input" placeholder="e.g. Technology" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-8">
                            <label class="d-block text-muted small mb-1">Icon (Bootstrap Icons class)</label>
                            <input type="text" name="icon" class="form-control pw-admin-input" placeholder="bi-cpu-fill" value="bi-mic-fill">
                        </div>
                        <div class="col-4">
                            <label class="d-block text-muted small mb-1">Color</label>
                            <input type="color" name="color" value="#8B5CF6" class="form-control pw-admin-input p-1" style="height:44px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn pw-btn-outline btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn pw-btn-primary btn-sm px-4">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
