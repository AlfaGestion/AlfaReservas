<div class="card mt-3">
    <div class="card-body">
        <ul class="nav nav-tabs" id="configTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="config-mp-tab" data-bs-toggle="tab" data-bs-target="#config-mp" type="button" role="tab">Mercado Pago</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="config-general-tab" data-bs-toggle="tab" data-bs-target="#config-general" type="button" role="tab">General</button>
            </li>
        </ul>

        <div class="tab-content pt-3">
            <div class="tab-pane fade show active" id="config-mp" role="tabpanel" aria-labelledby="config-mp-tab">
                <a href="<?= base_url('configMpView') ?>" type="button" class="btn btn-light border">
                    <img src="<?= base_url(PUBLIC_FOLDER . 'assets/images/mercado-pago.jfif') ?>" alt="Icono Mercado Pago" width="28" height="28"> Configurar Mercado Pago
                </a>
            </div>

            <div class="tab-pane fade" id="config-general" role="tabpanel" aria-labelledby="config-general-tab">
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="bookingEmailConfig" placeholder="Email para reservas" value="<?= isset($bookingEmail) ? esc($bookingEmail) : '' ?>">
                    <label for="bookingEmailConfig">Email para enviar reservas</label>
                </div>
                <button type="button" class="btn btn-success" id="saveConfigGeneral">Guardar configuracion</button>

                <hr class="my-4">

                <h6 class="mb-3">Estados por cliente</h6>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label for="estadoConfigClienteCodigo" class="form-label">Cliente</label>
                        <select class="form-select" id="estadoConfigClienteCodigo">
                            <option value="">Seleccionar cliente</option>
                            <?php foreach (($clientes ?? []) as $cliente) : ?>
                                <option value="<?= esc((string) ($cliente['codigo'] ?? '')) ?>">
                                    <?= esc((string) (($cliente['codigo'] ?? '') . ' - ' . ($cliente['razon_social'] ?? ''))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="estadoConfigForm" class="d-none">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label" for="cfg_trial_days">Dias en prueba</label>
                            <input type="number" class="form-control" id="cfg_trial_days" min="1" max="365" value="15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cfg_grace_days">Dias en gracia</label>
                            <input type="number" class="form-control" id="cfg_grace_days" min="0" max="60" value="5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="cfg_read_only_days">Dias solo lectura</label>
                            <input type="number" class="form-control" id="cfg_read_only_days" min="0" max="60" value="10">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" for="cfg_msg_trial">Texto en prueba</label>
                        <textarea class="form-control" id="cfg_msg_trial" rows="2"></textarea>
                    </div>
                    <div class="mt-2">
                        <label class="form-label" for="cfg_msg_grace">Texto en periodo de gracia</label>
                        <textarea class="form-control" id="cfg_msg_grace" rows="2"></textarea>
                    </div>
                    <div class="mt-2">
                        <label class="form-label" for="cfg_msg_read_only">Texto en solo lectura</label>
                        <textarea class="form-control" id="cfg_msg_read_only" rows="2"></textarea>
                    </div>
                    <div class="mt-2">
                        <label class="form-label" for="cfg_msg_suspended">Texto en suspendido</label>
                        <textarea class="form-control" id="cfg_msg_suspended" rows="2"></textarea>
                    </div>

                    <div class="small text-muted mt-2">
                        Placeholders disponibles: &lt;cliente&gt;, &lt;codigo&gt;, &lt;estado&gt;, &lt;plan&gt;, &lt;periodo&gt;, &lt;dias_restantes&gt;, &lt;fecha_fin&gt;, &lt;fecha_hoy&gt;.
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-secondary" id="resetClienteEstadoConfig">Restaurar defaults</button>
                        <button type="button" class="btn btn-success" id="saveClienteEstadoConfig">Guardar estados del cliente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
