<div class="d-flex justify-content-end mb-3">
    <button type="button" class="btn btn-success <?= ($isClientScoped ?? false) ? 'd-none' : '' ?>" data-bs-toggle="modal" data-bs-target="#nuevoClienteModal">
        <i class="fa-solid fa-user-plus me-1"></i> Nuevo cliente
    </button>
</div>

<style>
    #tableCustomers {
        border: 1px solid #d8e6f4;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    #clientesTableBody .cliente-row {
        transition: background-color .2s ease;
        cursor: pointer;
    }
    #clientesTableBody .cliente-row:hover {
        background: #f2f8ff;
    }
    #clientesTableBody .cliente-row.table-active td {
        background: #e9f3ff;
    }
    .action-cell .btn {
        white-space: nowrap;
    }
    .detail-grid strong {
        color: #1f4467;
    }
    .cfg-preview {
        background: #f8fafc;
        border: 1px dashed #b9cde4;
        border-radius: 8px;
        padding: 10px;
        min-height: 44px;
        color: #30506f;
        white-space: pre-line;
    }
    #clientesToastContainer {
        z-index: 1100;
    }
    .link-preview-inline {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    body.theme-dark #tableCustomers {
        border-color: #355d84;
        background: #132a40;
    }
    body.theme-dark #tableCustomers thead th {
        background: #1b3957;
        color: #d8e9fb;
        border-color: #3a6289;
    }
    body.theme-dark #tableCustomers tbody td {
        background: #17314b;
        color: #e7f1fb;
        border-color: #31557a;
    }
    body.theme-dark #tableCustomers tbody tr:nth-child(even) td {
        background: #1a3652;
    }
    body.theme-dark #tableCustomers tbody .cliente-row.table-active td {
        background: #2a4f72;
        color: #f1f7ff;
    }
    body.theme-dark #tableCustomers tbody .cliente-row.table-active a {
        color: #bfe2ff;
    }
    body.theme-dark #tableCustomers tbody .cliente-row.table-active .btn {
        opacity: 1;
    }
    body.theme-dark #tableCustomers tbody a {
        color: #9cd0ff;
    }
    body.theme-dark #tableCustomers tbody .btn-outline-primary {
        color: #93c8ff;
        border-color: #5ea6f6;
    }
    body.theme-dark #tableCustomers tbody .btn-outline-info {
        color: #8be3ff;
        border-color: #58bfdc;
    }
    body.theme-dark #tableCustomers tbody .btn-outline-warning {
        color: #ffd580;
        border-color: #ffc65c;
    }
    body.theme-dark #tableCustomers tbody .btn-outline-success {
        color: #8de3ab;
        border-color: #5ec985;
    }
</style>

<div id="clientesToastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

<div class="table-responsive-sm" id="tableCustomers">
    <table class="table align-middle mt-2">
        <thead>
            <tr>
                <th scope="col">Codigo</th>
                <th scope="col">Razon Social</th>
                <th scope="col">Base</th>
                <th scope="col">Rubro</th>
                <th scope="col">Email</th>
                <th scope="col">Link</th>
                <?php if (!($isClientScoped ?? false)) : ?>
                    <th scope="col">Acciones</th>
                <?php endif; ?>
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
                        data-plan-id="<?= esc((string) ($cliente['plan_id'] ?? '')) ?>"
                        data-periodo="<?= esc((string) ($cliente['contrato_periodo'] ?? '')) ?>"
                        data-included-users="<?= esc((string) ($cliente['included_users'] ?? '')) ?>"
                        data-included-resources="<?= esc((string) ($cliente['included_resources'] ?? '')) ?>"
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
                            <?php
                                $rawLink = trim((string) ($cliente['link'] ?? ''));
                                $publicLink = '';
                                $publicLabel = '';
                                if ($rawLink !== '') {
                                    $path = $rawLink;
                                    if (preg_match('#^https?://#i', $rawLink) === 1) {
                                        $parsedPath = parse_url($rawLink, PHP_URL_PATH);
                                        $path = is_string($parsedPath) ? $parsedPath : '';
                                    }
                                    $path = trim((string) preg_replace('#^/?index\.php/?#i', '', $path), '/');
                                    if ($path !== '') {
                                        $publicLink = base_url($path);
                                        $publicLabel = '/' . $path;
                                    } else {
                                        $publicLink = base_url(ltrim($rawLink, '/'));
                                        $publicLabel = (string) parse_url($publicLink, PHP_URL_PATH);
                                    }
                                }
                            ?>
                            <?php if ($publicLink !== '') : ?>
                                <a href="<?= esc($publicLink) ?>" target="_blank"><?= esc($publicLabel) ?></a>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                        <?php if (!($isClientScoped ?? false)) : ?>
                        <td class="action-cell">
                            <div class="d-flex gap-2 flex-wrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary edit-cliente btn-icon-label"
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
                                    data-plan-id="<?= esc((string) ($cliente['plan_id'] ?? '')) ?>"
                                    data-periodo="<?= esc((string) ($cliente['contrato_periodo'] ?? 'MONTH')) ?>"
                                    data-included-users="<?= esc((string) ($cliente['included_users'] ?? '1')) ?>"
                                    data-included-resources="<?= esc((string) ($cliente['included_resources'] ?? '2')) ?>"
                                >
                                    <i class="fa-solid fa-pen"></i> Editar
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-info config-cliente-estados btn-icon-label"
                                    data-codigo="<?= esc((string) ($cliente['codigo'] ?? '')) ?>"
                                    data-razon-social="<?= esc((string) ($cliente['razon_social'] ?? '')) ?>"
                                >
                                    <i class="fa-solid fa-sliders"></i> Configurar estados
                                </button>
                                <?php if ((int) ($cliente['habilitado'] ?? 0) === 1) : ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning toggle-cliente-status btn-icon-label" data-id="<?= esc((string) ($cliente['id'] ?? '')) ?>">
                                        <i class="fa-solid fa-user-slash"></i> Deshabilitar
                                    </button>
                                <?php else : ?>
                                    <button type="button" class="btn btn-sm btn-outline-success toggle-cliente-status btn-icon-label" data-id="<?= esc((string) ($cliente['id'] ?? '')) ?>">
                                        <i class="fa-solid fa-user-check"></i> Habilitar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?= ($isClientScoped ?? false) ? '6' : '7' ?>" class="text-center text-muted">No hay clientes cargados.</td>
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
            <div class="row g-2 detail-grid">
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

<div class="modal fade" id="clienteEstadoConfigModal" tabindex="-1" aria-labelledby="clienteEstadoConfigModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clienteEstadoConfigModalLabel">Configurar estados del cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="clienteEstadoCfgCodigo" value="">
                <div class="mb-2">
                    <strong>Cliente:</strong> <span id="clienteEstadoCfgTitle">-</span>
                </div>

                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label" for="clienteCfgTrialDays">Dias en prueba</label>
                        <input type="number" class="form-control" id="clienteCfgTrialDays" min="1" max="365" value="15">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="clienteCfgGraceDays">Dias en gracia</label>
                        <input type="number" class="form-control" id="clienteCfgGraceDays" min="0" max="60" value="5">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="clienteCfgReadOnlyDays">Dias solo lectura</label>
                        <input type="number" class="form-control" id="clienteCfgReadOnlyDays" min="0" max="60" value="10">
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label" for="clienteCfgMsgTrial">Texto en prueba</label>
                    <textarea class="form-control" id="clienteCfgMsgTrial" rows="2"></textarea>
                </div>
                <div class="mt-2">
                    <label class="form-label" for="clienteCfgMsgGrace">Texto en periodo de gracia</label>
                    <textarea class="form-control" id="clienteCfgMsgGrace" rows="2"></textarea>
                </div>
                <div class="mt-2">
                    <label class="form-label" for="clienteCfgMsgReadOnly">Texto en solo lectura</label>
                    <textarea class="form-control" id="clienteCfgMsgReadOnly" rows="2"></textarea>
                </div>
                <div class="mt-2">
                    <label class="form-label" for="clienteCfgMsgSuspended">Texto en suspendido</label>
                    <textarea class="form-control" id="clienteCfgMsgSuspended" rows="2"></textarea>
                </div>

                <div class="small text-muted mt-2">
                    <div class="cfg-placeholder-box">
                        Placeholders disponibles: &lt;cliente&gt;, &lt;codigo&gt;, &lt;estado&gt;, &lt;plan&gt;, &lt;periodo&gt;, &lt;dias_restantes&gt;, &lt;fecha_fin&gt;, &lt;fecha_hoy&gt;.
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label">Vista previa (gracia)</label>
                    <div class="cfg-preview" id="clienteCfgPreview">-</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="clienteCfgResetBtn">Restaurar defaults</button>
                <button type="button" class="btn btn-success" id="clienteCfgSaveBtn">Guardar configuracion</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="nuevoClienteModal" tabindex="-1" aria-labelledby="nuevoClienteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="clienteForm" action="<?= base_url('saveCliente') ?>" method="POST" onsubmit="return false;">
                <input type="hidden" id="cliente_id" name="id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoClienteModalLabel">Nuevo cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label for="codigo" class="form-label">Codigo</label>
                            <input type="text" class="form-control" id="codigo" value="<?= esc($nextClienteCodigo ?? '112010001') ?>" readonly>
                        </div>
                        <div class="col-md-8">
                            <label for="razon_social" class="form-label">Razon social</label>
                            <input type="text" class="form-control" id="razon_social" name="razon_social" required>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label for="base_preview" class="form-label">Base (generada)</label>
                            <input type="text" class="form-control" id="base_preview" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="link_path" class="form-label">Link publico</label>
                            <div class="input-group mb-1">
                                <span class="input-group-text">/</span>
                                <input type="text" class="form-control" id="link_path" name="link_path" placeholder="mi_cliente">
                            </div>
                            <div class="form-text link-preview-inline" id="link_preview">-</div>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-4">
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

                    <div class="row g-2 mt-1">
                        <div class="col-md-4">
                            <label for="plan_id" class="form-label">Plan</label>
                            <select class="form-select" id="plan_id" name="plan_id">
                                <option value="">Sin plan</option>
                                <?php foreach (($planes ?? []) as $plan) : ?>
                                    <option value="<?= esc((string) ($plan['id'] ?? '')) ?>"><?= esc((string) ($plan['nombre'] ?? $plan['codigo'] ?? 'Plan')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="contrato_periodo" class="form-label">Periodo</label>
                            <select class="form-select" id="contrato_periodo" name="contrato_periodo">
                                <option value="MONTH">Mensual</option>
                                <option value="YEAR">Anual</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="included_users" class="form-label">Usuarios</label>
                            <input type="number" min="0" class="form-control" id="included_users" name="included_users" value="1">
                        </div>
                        <div class="col-md-2">
                            <label for="included_resources" class="form-label">Recursos</label>
                            <input type="number" min="0" class="form-control" id="included_resources" name="included_resources" value="2">
                        </div>
                    </div>

                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label for="user_password" class="form-label">Contrasena acceso</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="user_password" name="user_password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="user_password" aria-label="Mostrar/ocultar contrasena">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="user_password_confirm" class="form-label">Repetir contrasena</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="user_password_confirm" name="user_password_confirm" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password-btn" data-target="user_password_confirm" aria-label="Mostrar/ocultar contrasena">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="form-text">En edicion, si lo dejas vacio, se mantiene la contrasena actual.</div>
                    <div class="form-text">Debe incluir al menos: 1 mayuscula, 1 minuscula y 1 numero.</div>

                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label for="nombre_apellido" class="form-label">Nombre y apellido (contacto)</label>
                            <input type="text" class="form-control" id="nombre_apellido" name="nombre_apellido" maxlength="255">
                        </div>
                        <div class="col-md-3">
                            <label for="telefono" class="form-label">Telefono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="30">
                        </div>
                        <div class="col-md-3">
                            <label for="dni" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni" name="dni" maxlength="20">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label for="localidad" class="form-label">Localidad</label>
                            <input type="text" class="form-control" id="localidad" name="localidad" maxlength="120">
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

        const baseWeb = <?= json_encode(rtrim((string) env('app.baseURL', base_url('/')), '/')) ?>.replace(/\/index\.php$/i, '');

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
