<div class="row mt-3 g-3">
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Nuevo rubro</h5>
                <form action="<?= base_url('saveRubro') ?>" method="POST">
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Ej: Veterinaria" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Guardar rubro</button>
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
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rubros)) : ?>
                                <?php foreach ($rubros as $rubro) : ?>
                                    <tr>
                                        <td><?= esc((string) ($rubro['id'] ?? '')) ?></td>
                                        <td><?= esc((string) ($rubro['descripcion'] ?? '')) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="2" class="text-muted text-center">No hay rubros cargados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
