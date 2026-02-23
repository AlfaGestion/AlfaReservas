<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
        <i class="fa-solid fa-user-plus me-1"></i> Nuevo cliente
    </button>
</div>

<div class="table-responsive-sm" id="tableCustomers">
    <table class="table align-middle table-striped-columns mt-2">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Codigo</th>
                <th scope="col">Razon Social</th>
                <th scope="col">Base</th>
                <th scope="col">Rubro</th>
                <th scope="col">Email</th>
                <th scope="col">Habilitado</th>
                <th scope="col">Link</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clientes)) : ?>
                <?php foreach ($clientes as $cliente) : ?>
                    <tr>
                        <td><?= esc($cliente['id']) ?></td>
                        <td><?= esc($cliente['codigo']) ?></td>
                        <td><?= esc($cliente['razon_social']) ?></td>
                        <td><?= esc($cliente['base']) ?></td>
                        <td><?= esc($cliente['rubro_descripcion'] ?? '-') ?></td>
                        <td><?= esc($cliente['email']) ?></td>
                        <td><?= ((int) ($cliente['habilitado'] ?? 0) === 1) ? 'Si' : 'No' ?></td>
                        <td>
                            <?php if (!empty($cliente['link'])) : ?>
                                <a href="<?= esc($cliente['link']) ?>" target="_blank"><?= esc($cliente['link']) ?></a>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" class="text-center text-muted">No hay clientes cargados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('saveCliente') ?>" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoClienteModalLabel">Nuevo cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="codigo" class="form-label">Codigo</label>
                        <input type="text" class="form-control" id="codigo" value="<?= esc($nextClienteCodigo ?? '112010001') ?>" readonly>
                    </div>
                    <div class="mb-2">
                        <label for="razon_social" class="form-label">Razon social</label>
                        <input type="text" class="form-control" id="razon_social" name="razon_social" required>
                    </div>
                    <div class="mb-2">
                        <label for="base" class="form-label">Base</label>
                        <input type="text" class="form-control" id="base" name="base" required>
                    </div>
                    <div class="mb-2">
                        <label for="id_rubro" class="form-label">Rubro</label>
                        <select class="form-select" id="id_rubro" name="id_rubro" required>
                            <?php if (!empty($rubros)) : ?>
                                <option value="">Seleccionar rubro</option>
                                <?php foreach ($rubros as $rubro) : ?>
                                    <option value="<?= esc($rubro['id']) ?>"><?= esc($rubro['descripcion']) ?></option>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <option value="">No hay rubros cargados</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" <?= empty($rubros) ? 'disabled' : '' ?>>Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
