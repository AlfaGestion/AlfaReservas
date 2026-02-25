<div id="generalButtons" class="mt-3">
    <?php if (session()->superadmin) : ?>
        <a type="button" href="<?= base_url('auth/register') ?>" class="btn btn-success mt-2 mb-2" id=""><i class="fa-solid fa-user-plus me-1"></i>Crear usuario</a>
        <button type="button" class="btn btn-outline-dark mt-2 mb-2" id="toggleConfigPanel"><i class="fa-solid fa-gear me-1"></i>Configuración</button>
    <?php endif; ?>

</div>

<?php if (!session()->superadmin) : ?>
    <div class="table-responsive-sm">
        <table class="table align-middle table-striped-columns mt-2">
            <thead>
                <tr>
                    <th scope="col">Porcentaje de reserva</th>
                    <th scope="col">Porcentaje de oferta</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= $rate['value'] ?>%</td>
                    <td><?= $offerRate['value'] ?>%</td>
                </tr>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Modal rate -->
<div class="modal fade" id="rateModal" tabindex="-1" aria-labelledby="rateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="rateModalLabel">Porcentaje de reserva</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">%</span>
                    <?php if ($rate) : ?>
                        <input type="text" class="form-control" placeholder="Ingresar porcentaje" name="rate" id="rate" aria-label="rate" aria-describedby="basic-addon1" value="<?= $rate['value'] ?>">
                    <?php else : ?>
                        <input type="text" class="form-control" placeholder="Ingresar porcentaje" name="rate" id="rate" aria-label="rate" aria-describedby="basic-addon1">
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-primary" id="saveRate">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal offer rate -->
<div class="modal fade" id="offerRateModal" tabindex="-1" aria-labelledby="offerRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="offerRateModalLabel">Porcentaje de oferta</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">%</span>
                    <?php if ($offerRate) : ?>
                        <input type="text" class="form-control" placeholder="Ingresar porcentaje" name="offerRate" id="offerRate" aria-label="offerRate" aria-describedby="basic-addon1" value="<?= $offerRate['value'] ?>">
                    <?php else : ?>
                        <input type="text" class="form-control" placeholder="Ingresar porcentaje" name="offerRate" id="offerRate" aria-label="offerRate" aria-describedby="basic-addon1">
                    <?php endif; ?>
                </div>

                <div class="form-floating">
                    <textarea class="form-control" placeholder="Leave a comment here" id="descriptionOffer"></textarea>
                    <label for="descriptionOffer">Añadir una descripción a la oferta</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="btn btn-primary" id="saveOfferRate">Guardar</button>
            </div>
        </div>
    </div>
</div>


<?php if (session()->superadmin) : ?>
    <div class="card mt-3 d-none" id="configPanel">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Configuración</h6>
                <button type="button" class="btn-close" aria-label="Close" id="closeConfigPanel"></button>
            </div>
            <ul class="nav nav-tabs" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="config-mp-tab" data-bs-toggle="tab" data-bs-target="#config-mp" type="button" role="tab">Mercado Pago</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="config-fondo-tab" data-bs-toggle="tab" data-bs-target="#config-fondo" type="button" role="tab">Fondo</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="config-general-tab" data-bs-toggle="tab" data-bs-target="#config-general" type="button" role="tab">General</button>
                </li>
            </ul>

            <div class="tab-content pt-3">
                <div class="tab-pane fade" id="config-mp" role="tabpanel">
                    <a href="<?= base_url('configMpView') ?>" type="button" class="btn btn-light">
                        <img src="<?= base_url(PUBLIC_FOLDER . 'assets/images/mercado-pago.jfif') ?>" alt="Icono Mercado Pago" width="10%" height="5%"> Configurar Mercado Pago
                    </a>
                </div>
                <div class="tab-pane fade" id="config-fondo" role="tabpanel">
                    <a href="<?= base_url('upload') ?>" type="button" class="btn btn-info"><i class="fa-solid fa-file-arrow-up me-1"></i>Cambiar fondo</a>
                </div>
                <div class="tab-pane fade show active" id="config-general" role="tabpanel">
                    <div class="form-floating mb-3">
                        <textarea class="form-control" id="closureTextConfig" style="height: 160px" placeholder="Texto de cierre"><?= isset($closureText) ? $closureText : '' ?></textarea>
                        <label for="closureTextConfig">Texto de cierre (usar &lt;fecha&gt;)</label>
                        <div class="form-text">Si escribís &lt;fecha&gt; se reemplaza por la fecha (dd/mm/yyyy).</div>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="bookingEmailConfig" placeholder="Email para reservas" value="<?= isset($bookingEmail) ? $bookingEmail : '' ?>">
                        <label for="bookingEmailConfig">Email para enviar reservas</label>
                    </div>
                    <button type="button" class="btn btn-success" id="saveConfigGeneral">Guardar configuración</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3 d-none" id="cancelReservationsPanel">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Cierre de cancha</h5>
                <button type="button" class="btn-close" aria-label="Close" id="closeCancelReservations"></button>
            </div>

            <div class="mt-3">
                <ul class="nav nav-tabs" id="cancelReservationsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="cancel-closures-new-tab" data-bs-toggle="tab" data-bs-target="#cancel-closures-new" type="button" role="tab">
                            Nuevo cierre
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="cancel-closures-list-tab" data-bs-toggle="tab" data-bs-target="#cancel-closures-list" type="button" role="tab">
                            Cierres programados
                        </button>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <div class="tab-pane fade show active" id="cancel-closures-new" role="tabpanel" aria-labelledby="cancel-closures-new-tab">
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="cancelDate">
                                    <label for="cancelDate">Fecha</label>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-floating">
                                    <select class="form-select" id="cancelField" aria-label="Cancha">
                                        <option value="all">Todas</option>
                                        <?php if (!empty($fields)) : ?>
                                            <?php foreach ($fields as $field) : ?>
                                                <option value="<?= $field['id'] ?>"><?= $field['name'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <label for="cancelField">Cancha</label>
                                </div>
                                <div id="cancelFieldHint" class="form-text"></div>
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="confirmCancelReservations">Aceptar</button>
                            <button type="button" class="btn btn-outline-secondary d-none" id="cancelEditCancelReservation">Cancelar edición</button>
                        </div>

                        <div id="cancelReservationsResult" class="mt-3"></div>
                        <div id="existingClosures" class="mt-3"></div>
                    </div>

                    <div class="tab-pane fade" id="cancel-closures-list" role="tabpanel" aria-labelledby="cancel-closures-list-tab">
                        <?= view('superadmin/tabClosures', ['fields' => $fields]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive-sm" id="tableCustomers">
        <table class="table align-middle table-striped-columns mt-2">
            <thead>
                <tr>
                    <th scope="col">Usuario</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Superadmin</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?= $user['user'] ?></td>
                        <td><?= $user['name'] ?></td>
                        <td><?= $user['superadmin'] == 1 ? 'Si' : 'No' ?></td>
                        <td>
                            <!-- <a href="#" class="btn btn-primary" id="editUser" data-id="<?= $user['id'] ?>">Editar</a> -->
                            <form action="<?= base_url('deleteUser/' . $user['id']) ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">
                                <?= csrf_field() ?> <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
