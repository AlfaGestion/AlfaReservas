<?php if (session()->superadmin) : ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-3">
        <div>
            <h6 class="mb-1">Usuarios</h6>
            <p class="text-muted mb-0">Gestiona accesos y altas desde un solo lugar.</p>
        </div>
        <a type="button" href="<?= base_url('auth/register') ?>" class="btn btn-success">
            <i class="fa-solid fa-user-plus me-1"></i>Crear usuario
        </a>
    </div>

    <div class="table-responsive-sm mt-3" id="tableCustomers">
        <table class="table align-middle table-striped-columns">
            <thead>
                <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Nombre</th>
                    <th scope="col"><?= !empty($usersFromTenant) ? 'Estado' : 'Superadmin' ?></th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= $user['user'] ?></td>
                        <td><?= $user['name'] ?? ($user['user'] ?? '-') ?></td>
                        <td>
                            <?php if (!empty($usersFromTenant)) : ?>
                                <?= ((int) ($user['active'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?>
                            <?php else : ?>
                                <?= ((int) ($user['superadmin'] ?? 0) === 1) ? 'Si' : 'No' ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm edit-user-btn" data-id="<?= $user['id'] ?>">
                                Editar
                            </button>
                            <form action="<?= base_url('deleteUser/' . $user['id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('Estas seguro de que deseas eliminar este usuario?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="editUserForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Editar usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label" for="editUserName">Usuario</label>
                            <input type="text" class="form-control" id="editUserName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editUserDisplayName">Nombre</label>
                            <input type="text" class="form-control" id="editUserDisplayName">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editUserEmail">Email</label>
                            <input type="email" class="form-control" id="editUserEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editUserCuenta">Cuenta</label>
                            <input type="text" class="form-control" id="editUserCuenta">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="editUserPassword">Nueva contrasena</label>
                            <input type="password" class="form-control" id="editUserPassword" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editUserSuperadmin">
                            <label class="form-check-label" for="editUserSuperadmin">Superadmin</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
