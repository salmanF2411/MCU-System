<?php
$page_title = 'Manajemen User - Sistem MCU';
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

requireLogin();
requireRole('super_admin');

// Get users
$query = "SELECT * FROM admin_users ORDER BY role, username";
$result = mysqli_query($conn, $query);
?>

<?php include '../../includes/admin-header.php'; ?>
<?php include '../includes/admin-nav.php'; ?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-lg-2">
            <?php include '../includes/admin-sidebar.php'; ?>
        </div>
        <div class="col-lg-10">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-user-cog me-2"></i> Manajemen User
                </h1>
                <a href="add.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i> Tambah User
                </a>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar User Admin</h5>
                </div>
                <div class="card-body">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Last Login</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td>
                                                <strong><?php echo $user['username']; ?></strong>
                                                <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                                    <span class="badge bg-info">Anda</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $user['nama_lengkap']; ?></td>
                                            <td><?php echo $user['email'] ?: '-'; ?></td>
                                            <td>
                                                <?php 
                                                $role_badges = [
                                                    'super_admin' => 'danger',
                                                    'pendaftaran' => 'primary',
                                                    'dokter_mata' => 'info',
                                                    'dokter_umum' => 'success'
                                                ];
                                                $role_names = [
                                                    'super_admin' => 'Super Admin',
                                                    'pendaftaran' => 'Pendaftaran',
                                                    'dokter_mata' => 'Dokter Mata',
                                                    'dokter_umum' => 'Dokter Umum'
                                                ];
                                                ?>
                                                <span class="badge bg-<?php echo $role_badges[$user['role']]; ?>">
                                                    <?php echo $role_names[$user['role']]; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['is_active']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login']): ?>
                                                    <?php echo formatDateIndo($user['last_login'], true); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">Belum login</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="edit.php?id=<?php echo $user['id']; ?>" 
                                                       class="btn btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    
                                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                        <?php if ($user['is_active']): ?>
                                                            <a href="toggle-status.php?id=<?php echo $user['id']; ?>&action=deactivate" 
                                                               class="btn btn-secondary" 
                                                               title="Nonaktifkan"
                                                               onclick="return confirm('Nonaktifkan user ini?')">
                                                                <i class="fas fa-ban"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="toggle-status.php?id=<?php echo $user['id']; ?>&action=activate" 
                                                               class="btn btn-success" 
                                                               title="Aktifkan"
                                                               onclick="return confirm('Aktifkan user ini?')">
                                                                <i class="fas fa-check"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-danger delete-user" 
                                                                data-id="<?php echo $user['id']; ?>"
                                                                data-name="<?php echo htmlspecialchars($user['nama_lengkap']); ?>"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada data user.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-trash me-2"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus user <strong id="userName"></strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Tindakan ini tidak dapat dibatalkan!
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST" action="delete.php" style="display: inline;">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Delete confirmation
document.querySelectorAll('.delete-user').forEach(button => {
    button.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const name = this.getAttribute('data-name');
        
        document.getElementById('deleteId').value = id;
        document.getElementById('userName').textContent = name;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    });
});
</script>

<?php include '../../includes/admin-footer.php'; ?>
