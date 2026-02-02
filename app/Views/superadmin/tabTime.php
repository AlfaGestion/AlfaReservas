<!-- horarios -->
<div class="openingTime mt-2" id="openingTime">
    <form action="<?= base_url('saveTime') ?>" method="POST">

        <h5 class="mb-2 mt-2">Configurar horarios de apertura</h5>

        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" role="switch" name="switchSunday" id="switchSunday" <?= $time['is_sunday'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="switchSunday">Cerrar los domingos</label>
        </div>
        <!-- <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" id="switchCutTime">
            <label class="form-check-label" for="switchCutTime">Horarios cortados</label>
        </div> -->

        <div class="input-group">
            <span class="input-group-text">Horarios de apertura</span>
            <input type="text" placeholder="Desde" id="changeTimeFrom" value="<?= $time['from'] ?>" name="from" class="form-control">
            <input type="text" placeholder="Hasta" id="changeTimeUntil" value="<?= $time['until'] ?>" name="until" class="form-control">
        </div>

        <div class="input-group mt-2" id="cutHours" style="display: none;">
            <span class="input-group-text">Horarios de apertura</span>
            <input type="text" placeholder="Desde" id="changeTimeFromCut" value="<?= $time['from_cut'] ?>" name="from_cut" class="form-control">
            <input type="text" placeholder="Hasta" id="changeTimeUntilCut" value="<?= $time['until_cut'] ?>" name="until_cut" class="form-control">
        </div>

        <h5 class="mb-2 mt-2">Configurar inicio de horario nocturno</h5>

        <div class="input-group mt-3 mb-3">
            <span class="input-group-text" id="basic-addon1">Inicio horario nocturno</span>
            <input type="text" class="form-control" id="horarioNocturno" value="<?= $time['nocturnal_time'] ?>" name="horarioNocturno" placeholder="Ingrese inicio de horario nocturno" aria-label="horarioNocturno" aria-describedby="basic-addon1">
        </div>

        <button type="submit" class="btn btn-success mt-2">Guardar</button>
        <a href="<?= base_url() ?>" type="button" class="btn btn-danger mt-2">Cancelar</a>
    </form>
</div>