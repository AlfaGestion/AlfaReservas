<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0">Cierres programados</h6>
</div>

<div class="row g-2 mb-3 align-items-end">
    <div class="col-lg-2 col-md-6">
        <div class="form-floating">
            <select class="form-select" id="closuresTabDateRange" aria-label="Rango">
                <option value="FD">Fecha del dia</option>
                <option value="MA" selected>Mes actual</option>
                <option value="MP">Mes pasado</option>
                <option value="SA">Semana actual</option>
                <option value="SP">Semana pasada</option>
            </select>
            <label for="closuresTabDateRange">Seleccione rango</label>
        </div>
    </div>
    <div class="col-lg-2 col-md-6">
        <div class="form-floating">
            <input type="date" class="form-control" id="closuresTabDateFrom">
            <label for="closuresTabDateFrom">Desde</label>
        </div>
    </div>
    <div class="col-lg-2 col-md-6">
        <div class="form-floating">
            <input type="date" class="form-control" id="closuresTabDateTo">
            <label for="closuresTabDateTo">Hasta</label>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="form-floating">
            <select class="form-select" id="closuresTabField" aria-label="Cancha">
                <option value="all">Todas</option>
                <?php if (!empty($fields)) : ?>
                    <?php foreach ($fields as $field) : ?>
                        <option value="<?= $field['id'] ?>"><?= $field['name'] ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <label for="closuresTabField">Cancha</label>
        </div>
    </div>
    <div class="col-lg-3 col-md-12 d-grid">
        <button type="button" class="btn btn-outline-secondary h-100" id="closuresTabSearch">
            <i class="fa-solid fa-magnifying-glass me-1"></i>Buscar
        </button>
    </div>
</div>

<div id="closuresTabList"></div>
