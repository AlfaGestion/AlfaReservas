<div class="card mt-3">
    <div class="card-body">
        <ul class="nav nav-tabs" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="config-mp-tab" data-bs-toggle="tab" data-bs-target="#config-mp" type="button" role="tab">Mercado Pago</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="config-general-tab" data-bs-toggle="tab" data-bs-target="#config-general" type="button" role="tab">General</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="config-users-tab" data-bs-toggle="tab" data-bs-target="#config-users" type="button" role="tab">Usuarios</button>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="config-mp" role="tabpanel" aria-labelledby="config-mp-tab">
                <a href="<?= base_url('configMpView') ?>" type="button" class="btn btn-light border">
                    <img src="<?= base_url(PUBLIC_FOLDER . 'assets/images/mercado-pago.jfif') ?>" alt="Icono Mercado Pago" width="28" height="28"> Configurar Mercado Pago
                </a>
            </div>

            <div class="tab-pane fade" id="config-general" role="tabpanel" aria-labelledby="config-general-tab">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="bookingEmailConfig" placeholder="Email para reservas" value="<?= isset($bookingEmail) ? esc($bookingEmail) : '' ?>">
                    <label for="bookingEmailConfig">Email para enviar reservas</label>
                </div>
                <button type="button" class="btn btn-success" id="saveConfigGeneral">Guardar configuracion</button>
            </div>

            <div class="tab-pane fade" id="config-users" role="tabpanel" aria-labelledby="config-users-tab">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="text-muted">Usuarios disponibles para administración.</div>
                    <button type="button" class="btn btn-success" id="openNewUserModalBtn" data-bs-toggle="modal" data-bs-target="#newUserModal">
                        <i class="fa-solid fa-user-plus me-1"></i> Nuevo usuario
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Perfil</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (!empty($users ?? [])) : ?>
                                <?php foreach (($users ?? []) as $u) : ?>
                                    <tr>
                                        <td><?= esc((string) ($u['user'] ?? '-')) ?></td>
                                        <td><?= esc((string) ($u['name'] ?? '-')) ?></td>
                                        <td><?= esc((string) ($u['email'] ?? '-')) ?></td>
                                        <td><?= ((int) ($u['superadmin'] ?? 0) === 1) ? 'Superadmin' : 'Admin' ?></td>
                                        <td><?= ((int) ($u['active'] ?? 0) === 1) ? 'Activo' : 'Inactivo' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay usuarios para mostrar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="modal fade" id="newUserModal" tabindex="-1" aria-labelledby="newUserModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="newUserModalLabel">Nuevo usuario</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body">
                                <form id="newUserForm">
                                    <div class="mb-3">
                                        <label for="newUserUser" class="form-label">Usuario</label>
                                        <input type="text" class="form-control" id="newUserUser" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newUserEmail" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="newUserEmail" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newUserPassword" class="form-label">Contrasena</label>
                                        <input type="password" class="form-control" id="newUserPassword" required>
                                    </div>
                                    <div class="mb-0">
                                        <label for="newUserRepeatPassword" class="form-label">Repetir contrasena</label>
                                        <input type="password" class="form-control" id="newUserRepeatPassword" required>
                                    </div>
                                    <div class="form-text mt-2">Debe incluir al menos: 1 mayuscula, 1 minuscula y 1 numero.</div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-success" id="saveNewUserBtn">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> Guardar usuario
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
