<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
        <i class="fa-solid fa-user-plus me-1"></i> Nuevo cliente
    </button>
</div>

<div class="table-responsive-sm" id="tableCustomers">
    <table class="table align-middle table-striped-columns mt-2">
        <thead>
            <tr>
                <th scope="col">Codigo</th>
                <th scope="col">Razon Social</th>
                <th scope="col">Base</th>
                <th scope="col">Rubro</th>
                <th scope="col">Email</th>
                <th scope="col">Link</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody id="clientesTableBody">
            <?php if (!empty($clientes)) : ?>
                <?php foreach ($clientes as $cliente) : ?>
                    <tr
                        class="cliente-row"
                        role="button"
                        data-id="<?= esc((string) ($cliente['id'] ?? '')) ?>"
                        data-codigo="<?= esc((string) ($cliente['codigo'] ?? '')) ?>"
                        data-razon-social="<?= esc((string) ($cliente['razon_social'] ?? '')) ?>"
                        data-base="<?= esc((string) ($cliente['base'] ?? '')) ?>"
                        data-rubro="<?= esc((string) ($cliente['rubro_descripcion'] ?? '')) ?>"
                        data-email="<?= esc((string) ($cliente['email'] ?? '')) ?>"
                        data-link="<?= esc((string) ($cliente['link'] ?? '')) ?>"
                        data-nombre-apellido="<?= esc((string) ($cliente['NombreApellido'] ?? '')) ?>"
                        data-telefono="<?= esc((string) ($cliente['telefono'] ?? '')) ?>"
                        data-dni="<?= esc((string) ($cliente['dni'] ?? '')) ?>"
                        data-localidad="<?= esc((string) ($cliente['localidad'] ?? '')) ?>"
                        data-estado="<?= esc((string) ($cliente['estado'] ?? 'TRIAL')) ?>"
                        data-habilitado="<?= esc((string) ((int) ($cliente['habilitado'] ?? 0))) ?>"
                        data-plan="<?= esc((string) ($cliente['plan_nombre'] ?? '')) ?>"
                        data-periodo="<?= esc((string) ($cliente['contrato_periodo'] ?? '')) ?>"
                        data-estado-contrato="<?= esc((string) ($cliente['contrato_estado'] ?? '')) ?>"
                        data-contrato-start="<?= esc((string) ($cliente['contrato_start_at'] ?? '')) ?>"
                        data-contrato-end="<?= esc((string) ($cliente['contrato_end_at'] ?? '')) ?>"
                        data-precio-total="<?= esc((string) ($cliente['precio_total'] ?? '')) ?>"
                    >
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
                        <td>
                            <?php if (!empty($cliente['link'])) : ?>
                                <a href="<?= esc($cliente['link']) ?>" target="_blank"><?= esc($cliente['link']) ?></a>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary edit-cliente"
                                    data-id="<?= esc((string) ($cliente['id'] ?? '')) ?>"
                                    data-razon-social="<?= esc((string) ($cliente['razon_social'] ?? '')) ?>"
                                    data-nombre-apellido="<?= esc((string) ($cliente['NombreApellido'] ?? '')) ?>"
                                    data-id-rubro="<?= esc((string) ($cliente['id_rubro'] ?? '')) ?>"
                                    data-email="<?= esc((string) ($cliente['email'] ?? '')) ?>"
                                    data-telefono="<?= esc((string) ($cliente['telefono'] ?? '')) ?>"
                                    data-dni="<?= esc((string) ($cliente['dni'] ?? '')) ?>"
                                    data-localidad="<?= esc((string) ($cliente['localidad'] ?? '')) ?>"
                                    data-estado="<?= esc((string) ($cliente['estado'] ?? 'TRIAL')) ?>"
                                    data-link="<?= esc((string) ($cliente['link'] ?? '')) ?>"
                                >
                                    Editar
                                </button>
                                <?php if ((int) ($cliente['habilitado'] ?? 0) === 1) : ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning toggle-cliente-status" data-id="<?= esc((string) ($cliente['id'] ?? '')) ?>">
                                        Deshabilitar
                                    </button>
                                <?php else : ?>
                                    <button type="button" class="btn btn-sm btn-outline-success toggle-cliente-status" data-id="<?= esc((string) ($cliente['id'] ?? '')) ?>">
                                        Habilitar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No hay clientes cargados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="card mt-3" id="clienteDetallePanel">
    <div class="card-body">
        <h6 class="card-title mb-3">Detalle del cliente seleccionado</h6>
        <div class="text-muted" id="clienteDetalleEmpty">Hace click en un cliente para ver más información.</div>
        <div id="clienteDetalleContent" class="d-none">
            <div class="row g-2">
                <div class="col-md-3"><strong>ID:</strong> <span id="detalle_id">-</span></div>
                <div class="col-md-3"><strong>Estado cliente:</strong> <span id="detalle_estado">-</span></div>
                <div class="col-md-3"><strong>Habilitado:</strong> <span id="detalle_habilitado">-</span></div>
                <div class="col-md-3"><strong>Contacto:</strong> <span id="detalle_contacto">-</span></div>
                <div class="col-md-3"><strong>Telefono:</strong> <span id="detalle_telefono">-</span></div>
                <div class="col-md-3"><strong>DNI:</strong> <span id="detalle_dni">-</span></div>
                <div class="col-md-3"><strong>Localidad:</strong> <span id="detalle_localidad">-</span></div>
                <div class="col-md-3"><strong>Plan:</strong> <span id="detalle_plan">-</span></div>
                <div class="col-md-3"><strong>Periodo:</strong> <span id="detalle_periodo">-</span></div>
                <div class="col-md-3"><strong>Estado contrato:</strong> <span id="detalle_estado_contrato">-</span></div>
                <div class="col-md-3"><strong>Vigencia:</strong> <span id="detalle_vigencia">-</span></div>
                <div class="col-md-3"><strong>Precio:</strong> <span id="detalle_precio">-</span></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="clienteForm" action="<?= base_url('saveCliente') ?>" method="POST" onsubmit="return false;">
                <input type="hidden" id="cliente_id" name="id" value="">
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
                    <div class="mb-2">
                        <label for="nombre_apellido" class="form-label">Nombre y apellido (contacto)</label>
                        <input type="text" class="form-control" id="nombre_apellido" name="nombre_apellido" maxlength="255">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="30">
                        </div>
                        <div class="col-md-6">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" maxlength="20">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-7">
                            <label for="localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="localidad" name="localidad" maxlength="120">
                        </div>
                        <div class="col-md-5">
                            <label for="estado" class="form-label">Estado cliente</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="TRIAL">En prueba</option>
                                <option value="ACTIVE">Activo</option>
                                <option value="GRACE">Periodo de gracia</option>
                                <option value="READ_ONLY">Solo lectura</option>
                                <option value="SUSPENDED">Suspendido</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="clienteSaveBtn" class="btn btn-primary" <?= empty($rubros) ? 'disabled' : '' ?>>Guardar</button>
                    <button type="button" id="clienteCancelEditBtn" class="btn btn-outline-secondary d-none">Cancelar edicion</button>
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
