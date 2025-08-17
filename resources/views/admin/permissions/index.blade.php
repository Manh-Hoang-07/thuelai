@extends('admin.index')

@section('page_title', 'Danh sách quyền')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Danh sách quyền</li>
@endsection

@section('content')
    <!--begin::App Content-->
    <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
                <div class="card">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-sm-9">
                                <!-- Form lọc -->
                                <form action="{{ route('admin.permissions.index') }}" method="GET" class="mb-0">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <input type="text" name="name" class="form-control" placeholder="🔍 Nhập tên quyền"
                                                   value="{{ request('name') }}">
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search"></i> Lọc
                                            </button>
                                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                                                <i class="bi bi-arrow-clockwise"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-sm-3 d-flex justify-content-end">
                                @can('access_users')
                                    <button type="button" class="btn btn-primary" onclick="openCreatePermissionModal()">
                                        <i class="bi bi-plus-circle"></i> Thêm Quyền
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- /.card-body -->
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ý nghĩa quyền</th>
                                <th>Tên quyền</th>
                                <th>Quyền cha</th>
                                <th>Mặc định</th>
                                <th class="text-center">Hành động</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($permissions ?? [] as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td>{{ $permission->title }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td>{{ $permission->parent->title ?? 'Không có' }}</td>
                                    <td>{{ $permission->is_default ? 'Có' : 'Không' }}</td>
                                    <td class="text-center">
                                        <div class="action-buttons">
                                            @can('access_permissions')
                                                <button type="button" class="btn-action btn-edit" title="Chỉnh sửa"
                                                        onclick="openEditPermissionModal({{ $permission->id }})">
                                                    <i class="bi bi-edit"></i>
                                                </button>
                                                <form action="{{ route('admin.permissions.delete', $permission->id) }}" method="POST"
                                                      style="display:inline;"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa không?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action btn-delete" title="Xóa">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Hiển thị phân trang -->
                    @include('vendor.pagination.pagination', ['paginator' => $permissions])
                </div>
                <!-- /.card -->
            </div>
            <!--end::Row-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::App Content-->
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/admin/universal-modal.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/admin/universal-modal.js') }}"></script>
<script>
// Đợi jQuery sẵn sàng trước khi khởi tạo Universal Modal
function waitForJQuery(callback) {
    if (typeof $ !== 'undefined') {
        callback();
    } else {
        // Nếu jQuery chưa sẵn sàng, chờ DOM ready
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof $ !== 'undefined') {
                callback();
            } else {
                // Nếu vẫn chưa có jQuery, chờ thêm một chút
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        callback();
                    } else {
                        console.error('jQuery is not loaded after waiting');
                    }
                }, 100);
            }
        });
    }
}

// Khởi tạo Universal Modal
waitForJQuery(function() {
    // Kiểm tra jQuery đã sẵn sàng
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    // Kiểm tra UniversalModal class đã sẵn sàng
    if (typeof UniversalModal === 'undefined') {
        console.error('UniversalModal class is not loaded');
        return;
    }
    
    // Khởi tạo Universal Modal cho Permissions
    if (!window.permissionsModal) {
        window.permissionsModal = new UniversalModal({
            modalId: 'permissionsModal',
            modalTitle: 'Quyền hạn',
            formId: 'permissionsForm',
            submitBtnId: 'permissionsSubmitBtn',
            createRoute: '{{ route("admin.permissions.store") }}',
            updateRoute: '{{ route("admin.permissions.update", ":id") }}',
            getDataRoute: '{{ route("admin.permissions.show", ":id") }}',
            successMessage: 'Thao tác quyền hạn thành công',
            errorMessage: 'Có lỗi xảy ra khi xử lý quyền hạn',
            viewPath: 'admin.permissions.form',
            viewData: {
                permissions: @json($permissions ?? [])
            },
            onSuccess: function(response, isEdit, id) {
                setTimeout(() => {
                    location.reload();
                }, 1500);
            }
        });
    }
});

// Global functions để gọi từ HTML
function openCreatePermissionModal() {
    if (window.permissionsModal) {
        window.permissionsModal.openCreateModal();
    } else {
        console.error('Permissions modal not initialized');
    }
}

function openEditPermissionModal(permissionId) {
    if (window.permissionsModal) {
        window.permissionsModal.openEditModal(permissionId);
    } else {
        console.error('Permissions modal not initialized');
    }
}
</script>

<script>
$(document).ready(function() {
    // Status select change
    $('.status-select').change(function() {
        const permissionId = $(this).data('permission-id');
        const newStatus = $(this).val();
        const currentStatus = $(this).data('current-status');
        
        if (newStatus === currentStatus) return;
        
        $.ajax({
            url: `/admin/permissions/${permissionId}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    // Cập nhật current status
                    $(this).data('current-status', newStatus);
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                    // Revert select
                    $(this).val(currentStatus);
                }
            }.bind(this),
            error: function() {
                showAlert('danger', 'Có lỗi xảy ra khi cập nhật trạng thái');
                // Revert select
                $(this).val(currentStatus);
            }.bind(this)
        });
    });

    // Featured select change
    $('.featured-select').change(function() {
        const permissionId = $(this).data('permission-id');
        const newFeatured = $(this).val();
        const currentFeatured = $(this).data('current-featured');
        
        if (newFeatured === currentFeatured) return;
        
        $.ajax({
            url: `/admin/permissions/${permissionId}/toggle-featured`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                is_featured: newFeatured
            },
            success: function(response) {
                if (response.success) {
                    // Cập nhật current featured
                    $(this).data('current-featured', newFeatured);
                    showAlert('success', response.message);
                } else {
                    showAlert('danger', response.message);
                    // Revert select
                    $(this).val(currentFeatured);
                }
            }.bind(this),
            error: function() {
                showAlert('danger', 'Có lỗi xảy ra khi cập nhật nổi bật');
                // Revert select
                $(this).val(currentFeatured);
            }.bind(this)
        });
    });
});

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Tạo container nếu chưa có
    if ($('#alert-container').length === 0) {
        $('.card-body').prepend('<div id="alert-container"></div>');
    }
    
    $('#alert-container').html(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('#alert-container .alert').fadeOut();
    }, 5000);
}
</script>
@endsection
