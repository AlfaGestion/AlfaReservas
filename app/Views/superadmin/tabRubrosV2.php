<div class="mt-3">
    <ul class="nav nav-tabs" id="rubrosTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="rubros-list-tab" data-bs-toggle="tab" data-bs-target="#rubros-list" type="button" role="tab">Rubros</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rubro-parametros-tab" data-bs-toggle="tab" data-bs-target="#rubro-parametros" type="button" role="tab">Rubro parametros</button>
        </li>
    </ul>

    <div class="tab-content pt-3">
        <div class="tab-pane fade show active" id="rubros-list" role="tabpanel" aria-labelledby="rubros-list-tab">
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">Nuevo rubro</h5>
                            <form id="rubroForm" action="<?= base_url('saveRubro') ?>" method="POST" onsubmit="return false;">
                                <input type="hidden" id="rubro_id" name="id" value="">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripcion</label>
                                    <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Ej: Veterinaria" required>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" id="rubroSaveBtn">Guardar rubro</button>
                                    <button type="button" class="btn btn-outline-secondary d-none" id="rubroCancelEditBtn">Cancelar edicion</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">Rubros cargados</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Descripcion</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rubrosTableBody">
                                        <?php if (!empty($rubros)) : ?>
                                            <?php foreach ($rubros as $rubro) : ?>
                                                <tr>
                                                    <td><?= esc((string) ($rubro['id'] ?? '')) ?></td>
                                                    <td><?= esc((string) ($rubro['descripcion'] ?? '')) ?></td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary edit-rubro"
                                                            data-id="<?= esc((string) ($rubro['id'] ?? '')) ?>"
                                                            data-descripcion="<?= esc((string) ($rubro['descripcion'] ?? '')) ?>"
                                                        >
                                                            Editar
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="3" class="text-muted text-center">No hay rubros cargados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="rubro-parametros" role="tabpanel" aria-labelledby="rubro-parametros-tab">
            <div class="row g-3">
                <div class="col-12 col-lg-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">Nuevo parametro</h5>
                            <form id="rubroParametroForm" action="<?= base_url('saveRubroParametro') ?>" method="POST" onsubmit="return false;">
                                <input type="hidden" id="param_id" name="id" value="">
                                <div class="mb-2">
                                    <label for="param_rubro_id" class="form-label">Rubro</label>
                                    <select id="param_rubro_id" name="rubro_id" class="form-select" required>
                                        <option value="">Seleccionar rubro</option>
                                        <?php foreach (($rubros ?? []) as $rubro) : ?>
                                            <option value="<?= esc((string) ($rubro['id'] ?? '')) ?>"><?= esc((string) ($rubro['descripcion'] ?? '')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label for="param_key" class="form-label">Key</label>
                                    <input type="text" id="param_key" name="key" class="form-control" placeholder="usuarios_extra" required>
                                </div>
                                <div class="mb-2">
                                    <label for="param_label" class="form-label">Label</label>
                                    <input type="text" id="param_label" name="label" class="form-control" placeholder="Usuarios extra" required>
                                </div>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="param_min" class="form-label">Min</label>
                                        <input type="number" id="param_min" name="min_value" class="form-control" value="1">
                                    </div>
                                    <div class="col-6">
                                        <label for="param_max" class="form-label">Max</label>
                                        <input type="number" id="param_max" name="max_value" class="form-control" value="999">
                                    </div>
                                </div>
                                <div class="mb-2 mt-2">
                                    <label for="param_precio" class="form-label">Precio por unidad</label>
                                    <input type="number" step="0.01" id="param_precio" name="precio_por_unidad" class="form-control" value="0">
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <button type="button" class="btn btn-primary" id="rubroParametroSaveBtn">Guardar parametro</button>
                                    <button type="button" class="btn btn-outline-secondary d-none" id="rubroParametroCancelEditBtn">Cancelar edicion</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-3">Parametros cargados</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Rubro</th>
                                            <th>Key</th>
                                            <th>Label</th>
                                            <th>Precio</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rubroParametrosTableBody">
                                        <?php if (!empty($rubroParametros)) : ?>
                                            <?php foreach ($rubroParametros as $param) : ?>
                                                <tr>
                                                    <td><?= esc((string) ($param['rubro_descripcion'] ?? '')) ?></td>
                                                    <td><?= esc((string) ($param['key'] ?? '')) ?></td>
                                                    <td><?= esc((string) ($param['label'] ?? '')) ?></td>
                                                    <td><?= esc((string) ($param['precio_por_unidad'] ?? '0')) ?></td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            class="btn btn-sm btn-outline-primary edit-rubro-parametro"
                                                            data-id="<?= esc((string) ($param['id'] ?? '')) ?>"
                                                            data-rubro-id="<?= esc((string) ($param['rubro_id'] ?? '')) ?>"
                                                            data-key="<?= esc((string) ($param['key'] ?? '')) ?>"
                                                            data-label="<?= esc((string) ($param['label'] ?? '')) ?>"
                                                            data-min-value="<?= esc((string) ($param['min_value'] ?? '1')) ?>"
                                                            data-max-value="<?= esc((string) ($param['max_value'] ?? '999')) ?>"
                                                            data-precio-por-unidad="<?= esc((string) ($param['precio_por_unidad'] ?? '0')) ?>"
                                                        >
                                                            Editar
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="5" class="text-muted text-center">No hay parametros cargados.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
