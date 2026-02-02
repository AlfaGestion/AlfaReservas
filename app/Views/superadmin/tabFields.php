<div class="fieldsButtons mt-3">
    <button type="submit" id="buttonCreateField" class="btn btn-success"><i class="fa-solid fa-plus me-1"></i>Crear</button>
    <button type="submit" id="buttonEditField" class="btn btn-warning"><i class="fa-solid fa-pen-to-square me-1"></i>Editar</button>
</div>

<div class="enterFields d-none" id="enterFields">
    <form action="<?= base_url('saveField') ?>" method="POST">
        <div class="input-group mt-3 mb-3">
            <span class="input-group-text" id="basic-addon1">Nombre cancha</span>
            <input type="text" class="form-control" name="nombre" placeholder="Ingrese el nombre de la cancha" aria-label="Nombre cancha" aria-describedby="basic-addon1">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon3">Medidas</span>
            <input type="text" class="form-control" name="medidas" placeholder="Ingrese las medidas de la cancha" aria-label="Medidas" aria-describedby="basic-addon3">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon2">Tipo de piso</span>
            <input type="text" class="form-control" name="tipoPiso" placeholder="Ingrese el tipo de piso de la cancha" aria-label="Tipo piso" aria-describedby="basic-addon2">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon4">Tipo de cancha</span>
            <input type="text" class="form-control" name="tipoCancha" placeholder="Ingrese el tipo de cancha (fútbol 5, 7, 11)" aria-label="Tipo cancha" aria-describedby="basic-addon4">
        </div>

        <div class="form-check form-switch d-none">
            <input class="form-check-input" type="checkbox" name="iluminacion" role="switch" id="iluminacion">
            <label class="form-check-label" for="iluminacion">Tiene iluminación</label>
        </div>

        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="tipoTecho" role="switch" id="tipoTecho">
            <label class="form-check-label" for="tipoTecho">Es techada</label>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text">Valor sin iluminación</span>
            <input type="text" class="form-control" name="valor" placeholder="Ingrese valor por hora sin iluminación" aria-label="Valor">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text">Valor con iluminación</span>
            <input type="text" class="form-control" name="valorIluminacion" placeholder="Ingrese valor por hora con iluminación" aria-label="Valor">
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="<?= base_url() ?>" type="button" class="btn btn-danger">Cancelar</a>
    </form>
</div>

<div class="form-floating d-none mt-3" id="selectEditField">
  <select class="form-select" id="selectEditFields" aria-label="Floating label select example">
    <option value="">Seleccionar</option>
    <?php foreach($fields as $field) : ?>
        <option value="<?= $field['id'] ?>"><?= $field['name'] ?></option>
    <?php endforeach ; ?>
  </select>
  <label for="selectEditFields">Editar cancha</label>
</div>

<div id="editFieldDiv">
    
</div>