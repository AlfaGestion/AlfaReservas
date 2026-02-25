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
                        <td><?php
                            $rubroDesc = strtolower(trim((string) ($cliente['rubro_descripcion'] ?? '')));
                            echo esc(match ($rubroDesc) {
                                'cancha', 'canchas' => '🏟 Canchas',
                                'peluqueria', 'peluquería' => '💇 Peluquería',
                                'consultorio', 'consultorios' => '🏥 Consultorio',
                                'gimnasio', 'gimnasios' => '🏋 Gimnasio',
                                'comida', 'restaurante', 'restaurantes', 'pedidos' => '🍽 Pedidos',
                                default => ($cliente['rubro_descripcion'] ?? '-'),
                            });
                        ?></td>
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
                        <label for="base_preview" class="form-label">Base (generada desde razón social)</label>
                        <input type="text" class="form-control" id="base_preview" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Link completo</label>
                        <div class="small text-muted mb-2" id="link_preview">-</div>
                        <div class="input-group">
                            <span class="input-group-text">/</span>
                            <input type="text" class="form-control" id="link_path" name="link_path" placeholder="mi_cliente">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="id_rubro" class="form-label">Rubro</label>
                        <select class="form-select" id="id_rubro" name="id_rubro" required>
                            <?php if (!empty($rubros)) : ?>
                                <option value="">Seleccionar rubro</option>
                                <?php foreach ($rubros as $rubro) : ?>
                                    <?php
                                    $rubroDesc = strtolower(trim((string) ($rubro['descripcion'] ?? '')));
                                    $rubroLabel = match ($rubroDesc) {
                                        'cancha', 'canchas' => '🏟 Canchas',
                                        'peluqueria', 'peluquería' => '💇 Peluquería',
                                        'consultorio', 'consultorios' => '🏥 Consultorio',
                                        'gimnasio', 'gimnasios' => '🏋 Gimnasio',
                                        'comida', 'restaurante', 'restaurantes', 'pedidos' => '🍽 Pedidos',
                                        default => ($rubro['descripcion'] ?? '-'),
                                    };
                                    ?>
                                    <option value="<?= esc($rubro['id']) ?>"><?= esc($rubroLabel) ?></option>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const razonSocialInput = document.getElementById('razon_social');
        const basePreviewInput = document.getElementById('base_preview');
        const linkPreviewInput = document.getElementById('link_preview');
        const linkPathInput = document.getElementById('link_path');
        if (!razonSocialInput || !basePreviewInput || !linkPreviewInput) {
            return;
        }

        const baseWeb = <?= json_encode(rtrim((string) env('app.baseURL', base_url('/')), '/')) ?>;

        const normalizeKey = (value) => {
            return value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '')
                .slice(0, 90);
        };

        const updatePreviews = () => {
            const key = normalizeKey(razonSocialInput.value || '');
            if (!linkPathInput.dataset.touched || linkPathInput.dataset.touched !== '1') {
                linkPathInput.value = key;
            }
            const path = normalizeKey(linkPathInput.value || '');
            linkPathInput.value = path;
            basePreviewInput.value = key;
            linkPreviewInput.textContent = path ? (baseWeb + '/' + path) : '-';
        };

        razonSocialInput.addEventListener('input', updatePreviews);
        linkPathInput.addEventListener('input', function () {
            linkPathInput.dataset.touched = '1';
            updatePreviews();
        });
        updatePreviews();
    });
</script>
