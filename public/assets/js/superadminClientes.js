function clienteRubroLabel(desc) {
    var v = String(desc || '').toLowerCase().trim();
    if (v === 'cancha' || v === 'canchas') return '🏟 Canchas';
    if (v === 'peluqueria' || v === 'peluquería') return '💇 Peluquería';
    if (v === 'consultorio' || v === 'consultorios') return '🏥 Consultorio';
    if (v === 'gimnasio' || v === 'gimnasios') return '🏋 Gimnasio';
    if (v === 'comida' || v === 'restaurante' || v === 'restaurantes' || v === 'pedidos') return '🍽 Pedidos';
    return desc || '-';
}

function escHtml(text) {
    return String(text == null ? '' : text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function showClientesToast(message, type) {
    var container = document.getElementById('clientesToastContainer');
    if (!container || typeof bootstrap === 'undefined') {
        alert(message);
        return;
    }

    var tone = type || 'info';
    var bg = tone === 'success' ? 'text-bg-success' : (tone === 'danger' ? 'text-bg-danger' : 'text-bg-primary');
    var toastEl = document.createElement('div');
    toastEl.className = 'toast align-items-center border-0 ' + bg;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = '' +
        '<div class="d-flex">' +
        '  <div class="toast-body">' + escHtml(message || '') + '</div>' +
        '  <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
        '</div>';

    container.appendChild(toastEl);
    var toast = new bootstrap.Toast(toastEl, { delay: 2200 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', function () { toastEl.remove(); });
}

var CLIENTE_ESTADO_CFG_DEFAULTS = {
    trial_days: 15,
    grace_days: 5,
    read_only_days: 10,
    msg_trial: 'Periodo de prueba activo. Te quedan <dias_restantes> dia(s). Vence el <fecha_fin>.',
    msg_grace: 'Estas en periodo de gracia. Te quedan <dias_restantes> dia(s) para regularizar el plan.',
    msg_read_only: 'Modo solo lectura activo. Te quedan <dias_restantes> dia(s) antes de la suspension.',
    msg_suspended: 'Tu cuenta esta suspendida por falta de pago. Contacta al administrador para reactivarla.'
};

function setClienteEstadoCfgForm(values) {
    var v = values || CLIENTE_ESTADO_CFG_DEFAULTS;
    var trial = document.getElementById('clienteCfgTrialDays');
    var grace = document.getElementById('clienteCfgGraceDays');
    var readOnly = document.getElementById('clienteCfgReadOnlyDays');
    var msgTrial = document.getElementById('clienteCfgMsgTrial');
    var msgGrace = document.getElementById('clienteCfgMsgGrace');
    var msgReadOnly = document.getElementById('clienteCfgMsgReadOnly');
    var msgSuspended = document.getElementById('clienteCfgMsgSuspended');
    if (trial) trial.value = Number(v.trial_days || 15);
    if (grace) grace.value = Number(v.grace_days || 5);
    if (readOnly) readOnly.value = Number(v.read_only_days || 10);
    if (msgTrial) msgTrial.value = v.msg_trial || '';
    if (msgGrace) msgGrace.value = v.msg_grace || '';
    if (msgReadOnly) msgReadOnly.value = v.msg_read_only || '';
    if (msgSuspended) msgSuspended.value = v.msg_suspended || '';
}

function updateEstadoPreview() {
    var preview = document.getElementById('clienteCfgPreview');
    if (!preview) return;
    var raw = (document.getElementById('clienteCfgMsgGrace') || {}).value || CLIENTE_ESTADO_CFG_DEFAULTS.msg_grace;
    var codigo = (document.getElementById('clienteEstadoCfgCodigo') || {}).value || '112010001';
    var txt = String(raw)
        .replace(/<cliente>/gi, 'Cliente Demo')
        .replace(/<codigo>/gi, codigo)
        .replace(/<estado>/gi, 'Periodo de gracia')
        .replace(/<plan>/gi, 'Pro')
        .replace(/<periodo>/gi, 'Mensual')
        .replace(/<dias_restantes>/gi, '4')
        .replace(/<fecha_fin>/gi, '2026-03-02')
        .replace(/<fecha_hoy>/gi, '2026-02-26');
    preview.textContent = txt || '-';
}

async function loadClienteEstadoCfg(codigo) {
    if (!codigo) return;

    var response = await fetch(baseUrl + 'getClienteEstadoConfigAjax', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ clienteCodigo: codigo })
    });
    var responseData = await response.json();
    if (!response.ok || responseData.error) {
        throw new Error(responseData.message || 'No se pudo cargar la configuracion.');
    }

    setClienteEstadoCfgForm((responseData.data && responseData.data.config) ? responseData.data.config : CLIENTE_ESTADO_CFG_DEFAULTS);
    updateEstadoPreview();
}

async function saveClienteEstadoCfg() {
    var codigoEl = document.getElementById('clienteEstadoCfgCodigo');
    var codigo = codigoEl ? String(codigoEl.value || '').trim() : '';
    if (!codigo) {
        showClientesToast('No se encontro el codigo del cliente.', 'danger');
        return;
    }

    var payload = {
        clienteCodigo: codigo,
        trial_days: Number((document.getElementById('clienteCfgTrialDays') || {}).value || 15),
        grace_days: Number((document.getElementById('clienteCfgGraceDays') || {}).value || 5),
        read_only_days: Number((document.getElementById('clienteCfgReadOnlyDays') || {}).value || 10),
        msg_trial: (document.getElementById('clienteCfgMsgTrial') || {}).value || '',
        msg_grace: (document.getElementById('clienteCfgMsgGrace') || {}).value || '',
        msg_read_only: (document.getElementById('clienteCfgMsgReadOnly') || {}).value || '',
        msg_suspended: (document.getElementById('clienteCfgMsgSuspended') || {}).value || ''
    };

    var response = await fetch(baseUrl + 'saveClienteEstadoConfigAjax', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    var responseData = await response.json();
    if (!response.ok || responseData.error) {
        throw new Error(responseData.message || 'No se pudo guardar la configuracion.');
    }
}

function valueOrDash(v) {
    var t = String(v == null ? '' : v).trim();
    return t === '' ? '-' : t;
}

function normalizeClientePublicLink(link) {
    var raw = String(link || '').trim();
    if (!raw) return { href: '', label: '' };

    var path = raw;
    if (/^https?:\/\//i.test(raw)) {
        try {
            var u = new URL(raw);
            path = u.pathname || '';
        } catch (e) {
            path = raw;
        }
    }

    path = String(path || '')
        .replace(/^\/?index\.php\/?/i, '')
        .replace(/^\/+|\/+$/g, '');

    if (!path) return { href: '', label: '' };
    return { href: '/' + path, label: '/' + path };
}

function humanClienteEstado(v) {
    var s = String(v || '').toUpperCase().trim();
    if (s === 'TRIAL') return 'En prueba';
    if (s === 'ACTIVE') return 'Activo';
    if (s === 'GRACE') return 'Periodo de gracia';
    if (s === 'READ_ONLY') return 'Solo lectura';
    if (s === 'SUSPENDED') return 'Suspendido';
    return valueOrDash(v);
}

function humanContratoEstado(v) {
    var s = String(v || '').toUpperCase().trim();
    if (s === 'PENDING') return 'Pendiente';
    if (s === 'ACTIVE') return 'Activo';
    if (s === 'CANCELED') return 'Cancelado';
    if (s === 'EXPIRED') return 'Vencido';
    return valueOrDash(v);
}

function humanPeriodo(v) {
    var s = String(v || '').toUpperCase().trim();
    if (s === 'MONTH') return 'Mensual';
    if (s === 'YEAR') return 'Anual';
    return valueOrDash(v);
}

function setText(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = valueOrDash(value);
}

function showClienteDetailFromDataset(data) {
    var empty = document.getElementById('clienteDetalleEmpty');
    var content = document.getElementById('clienteDetalleContent');
    if (empty) empty.classList.add('d-none');
    if (content) content.classList.remove('d-none');

    var vigencia = '-';
    if ((data.contratoStart || '') !== '' || (data.contratoEnd || '') !== '') {
        vigencia = valueOrDash(data.contratoStart) + ' a ' + valueOrDash(data.contratoEnd);
    }

    setText('detalle_id', data.id);
    setText('detalle_estado', humanClienteEstado(data.estado));
    setText('detalle_habilitado', String(data.habilitado) === '1' ? 'Si' : 'No');
    setText('detalle_contacto', data.nombreApellido);
    setText('detalle_telefono', data.telefono);
    setText('detalle_dni', data.dni);
    setText('detalle_localidad', data.localidad);
    setText('detalle_plan', data.plan);
    setText('detalle_periodo', humanPeriodo(data.periodo));
    setText('detalle_estado_contrato', humanContratoEstado(data.estadoContrato));
    setText('detalle_vigencia', vigencia);
    setText('detalle_precio', data.precioTotal);
}

function clearClienteSelection() {
    document.querySelectorAll('#clientesTableBody .cliente-row.table-active').forEach(function (row) {
        row.classList.remove('table-active');
    });
}

function renderClientesTable(clientes) {
    var tbody = document.getElementById('clientesTableBody');
    if (!tbody) return;

    if (!clientes || clientes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay clientes cargados.</td></tr>';
        return;
    }

    tbody.innerHTML = clientes.map(function (cliente) {
        var habilitado = Number(cliente.habilitado || 0) === 1;
        var normLink = normalizeClientePublicLink(cliente.link || '');
        var link = normLink.href ? '<a href="' + escHtml(normLink.href) + '" target="_blank">' + escHtml(normLink.label) + '</a>' : '-';
        return '<tr class="cliente-row" role="button"' +
            ' data-id="' + escHtml(cliente.id) + '"' +
            ' data-codigo="' + escHtml(cliente.codigo) + '"' +
            ' data-razon-social="' + escHtml(cliente.razon_social) + '"' +
            ' data-base="' + escHtml(cliente.base) + '"' +
            ' data-rubro="' + escHtml(cliente.rubro_descripcion || '') + '"' +
            ' data-email="' + escHtml(cliente.email) + '"' +
            ' data-link="' + escHtml(cliente.link || '') + '"' +
            ' data-nombre-apellido="' + escHtml(cliente.NombreApellido || '') + '"' +
            ' data-telefono="' + escHtml(cliente.telefono || '') + '"' +
            ' data-dni="' + escHtml(cliente.dni || '') + '"' +
            ' data-localidad="' + escHtml(cliente.localidad || '') + '"' +
            ' data-estado="' + escHtml(cliente.estado || 'TRIAL') + '"' +
            ' data-habilitado="' + (habilitado ? '1' : '0') + '"' +
            ' data-plan="' + escHtml(cliente.plan_nombre || '') + '"' +
            ' data-plan-id="' + escHtml(cliente.plan_id || '') + '"' +
            ' data-periodo="' + escHtml(cliente.contrato_periodo || '') + '"' +
            ' data-included-users="' + escHtml(cliente.included_users || '') + '"' +
            ' data-included-resources="' + escHtml(cliente.included_resources || '') + '"' +
            ' data-estado-contrato="' + escHtml(cliente.contrato_estado || '') + '"' +
            ' data-contrato-start="' + escHtml(cliente.contrato_start_at || '') + '"' +
            ' data-contrato-end="' + escHtml(cliente.contrato_end_at || '') + '"' +
            ' data-precio-total="' + escHtml(cliente.precio_total || '') + '"' +
            '>' +
            '<td>' + escHtml(cliente.codigo) + '</td>' +
            '<td>' + escHtml(cliente.razon_social) + '</td>' +
            '<td>' + escHtml(cliente.base) + '</td>' +
            '<td>' + escHtml(clienteRubroLabel(cliente.rubro_descripcion)) + '</td>' +
            '<td>' + escHtml(cliente.email) + '</td>' +
            '<td>' + link + '</td>' +
            '<td class="action-cell"><div class="d-flex gap-2 flex-wrap">' +
            '<button type="button" class="btn btn-sm btn-outline-primary edit-cliente btn-icon-label"' +
            ' data-id="' + escHtml(cliente.id) + '"' +
            ' data-razon-social="' + escHtml(cliente.razon_social) + '"' +
            ' data-nombre-apellido="' + escHtml(cliente.NombreApellido || '') + '"' +
            ' data-id-rubro="' + escHtml(cliente.id_rubro) + '"' +
            ' data-email="' + escHtml(cliente.email) + '"' +
            ' data-telefono="' + escHtml(cliente.telefono || '') + '"' +
            ' data-dni="' + escHtml(cliente.dni || '') + '"' +
            ' data-localidad="' + escHtml(cliente.localidad || '') + '"' +
            ' data-estado="' + escHtml(cliente.estado || 'TRIAL') + '"' +
            ' data-link="' + escHtml(cliente.link || '') + '"' +
            ' data-plan-id="' + escHtml(cliente.plan_id || '') + '"' +
            ' data-periodo="' + escHtml(cliente.contrato_periodo || 'MONTH') + '"' +
            ' data-included-users="' + escHtml(cliente.included_users || '1') + '"' +
            ' data-included-resources="' + escHtml(cliente.included_resources || '2') + '"' +
            '><i class="fa-solid fa-pen"></i> Editar</button>' +
            '<button type="button" class="btn btn-sm btn-outline-info config-cliente-estados btn-icon-label"' +
            ' data-codigo="' + escHtml(cliente.codigo || '') + '"' +
            ' data-razon-social="' + escHtml(cliente.razon_social || '') + '"' +
            '><i class="fa-solid fa-sliders"></i> Configurar estados</button>' +
            (habilitado
                ? '<button type="button" class="btn btn-sm btn-outline-warning toggle-cliente-status btn-icon-label" data-id="' + escHtml(cliente.id) + '"><i class="fa-solid fa-user-slash"></i> Deshabilitar</button>'
                : '<button type="button" class="btn btn-sm btn-outline-success toggle-cliente-status btn-icon-label" data-id="' + escHtml(cliente.id) + '"><i class="fa-solid fa-user-check"></i> Habilitar</button>') +
            '</div></td>' +
            '</tr>';
    }).join('');
}

function resetClienteForm(nextCode) {
    var form = document.getElementById('clienteForm');
    if (!form) return;
    form.reset();
    var id = document.getElementById('cliente_id');
    var save = document.getElementById('clienteSaveBtn');
    var cancel = document.getElementById('clienteCancelEditBtn');
    var title = document.getElementById('nuevoClienteModalLabel');
    var codigo = document.getElementById('codigo');
    var linkPath = document.getElementById('link_path');
    var estado = document.getElementById('estado');
    var planId = document.getElementById('plan_id');
    var contratoPeriodo = document.getElementById('contrato_periodo');
    var includedUsers = document.getElementById('included_users');
    var includedResources = document.getElementById('included_resources');
    var userPassword = document.getElementById('user_password');
    var userPasswordConfirm = document.getElementById('user_password_confirm');
    if (id) id.value = '';
    if (save) save.textContent = 'Guardar';
    if (cancel) cancel.classList.add('d-none');
    if (title) title.textContent = 'Nuevo cliente';
    if (codigo && nextCode) codigo.value = nextCode;
    if (estado) estado.value = 'TRIAL';
    if (planId) planId.value = '';
    if (contratoPeriodo) contratoPeriodo.value = 'MONTH';
    if (includedUsers) includedUsers.value = '1';
    if (includedResources) includedResources.value = '2';
    if (userPassword) {
        userPassword.value = '';
        userPassword.type = 'password';
        userPassword.required = true;
        userPassword.placeholder = '';
    }
    if (userPasswordConfirm) {
        userPasswordConfirm.value = '';
        userPasswordConfirm.type = 'password';
        userPasswordConfirm.required = true;
        userPasswordConfirm.placeholder = '';
    }
    if (linkPath) {
        linkPath.dataset.touched = '';
        if (typeof Event === 'function') {
            linkPath.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }
}

function saveClienteAjax() {
    var form = document.getElementById('clienteForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;
    var passInput = document.getElementById('user_password');
    var passConfirmInput = document.getElementById('user_password_confirm');
    var password = passInput ? String(passInput.value || '') : '';
    var passwordConfirm = passConfirmInput ? String(passConfirmInput.value || '') : '';
    var clienteIdInput = document.getElementById('cliente_id');
    var isEdit = !!(clienteIdInput && String(clienteIdInput.value || '').trim() !== '');
    var hasOnePassword = password !== '' || passwordConfirm !== '';
    var passwordRule = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;

    if (!isEdit || hasOnePassword) {
        if (!passwordRule.test(password)) {
            showClientesToast('La contrasena debe tener al menos una mayuscula, una minuscula y un numero.', 'danger');
            return;
        }
    }

    fetch(baseUrl + 'saveClienteAjax', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, json: j }; }); })
        .then(function (result) {
            if (!result.ok || result.json.error) return showClientesToast(result.json.message || 'No se pudo guardar el cliente.', 'danger');
            var data = result.json.data || {};
            renderClientesTable(data.clientes || []);
            resetClienteForm(data.nextClienteCodigo || '');
            var modalEl = document.getElementById('nuevoClienteModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                var instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                instance.hide();
            }
            showClientesToast('Cliente guardado correctamente.', 'success');
        }).catch(function () { showClientesToast('No se pudo guardar el cliente.', 'danger'); });
}

function toggleClienteStatusAjax(id) {
    var fd = new FormData();
    fd.append('id', String(id || ''));
    fetch(baseUrl + 'toggleClienteStatusAjax', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: fd
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, json: j }; }); })
        .then(function (result) {
            if (!result.ok || result.json.error) return showClientesToast(result.json.message || 'No se pudo actualizar el estado.', 'danger');
            var data = result.json.data || {};
            renderClientesTable(data.clientes || []);
            showClientesToast(result.json.message || 'Estado actualizado.', 'success');
        }).catch(function () { showClientesToast('No se pudo actualizar el estado.', 'danger'); });
}

document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'clienteSaveBtn') {
        saveClienteAjax();
        return;
    }
    if (e.target && e.target.id === 'clienteCancelEditBtn') {
        resetClienteForm();
        return;
    }

    var editBtn = e.target.closest('.edit-cliente');
    if (editBtn) {
        var id = document.getElementById('cliente_id');
        var razon = document.getElementById('razon_social');
        var rubro = document.getElementById('id_rubro');
        var email = document.getElementById('email');
        var linkPath = document.getElementById('link_path');
        var nombreApellido = document.getElementById('nombre_apellido');
        var telefono = document.getElementById('telefono');
        var dni = document.getElementById('dni');
        var localidad = document.getElementById('localidad');
        var estado = document.getElementById('estado');
        var planId = document.getElementById('plan_id');
        var contratoPeriodo = document.getElementById('contrato_periodo');
        var includedUsers = document.getElementById('included_users');
        var includedResources = document.getElementById('included_resources');
        var userPassword = document.getElementById('user_password');
        var userPasswordConfirm = document.getElementById('user_password_confirm');
        var title = document.getElementById('nuevoClienteModalLabel');
        var save = document.getElementById('clienteSaveBtn');
        var cancel = document.getElementById('clienteCancelEditBtn');
        if (id) id.value = editBtn.dataset.id || '';
        if (razon) razon.value = editBtn.dataset.razonSocial || '';
        if (rubro) rubro.value = editBtn.dataset.idRubro || '';
        if (email) email.value = editBtn.dataset.email || '';
        if (nombreApellido) nombreApellido.value = editBtn.dataset.nombreApellido || '';
        if (telefono) telefono.value = editBtn.dataset.telefono || '';
        if (dni) dni.value = editBtn.dataset.dni || '';
        if (localidad) localidad.value = editBtn.dataset.localidad || '';
        if (estado) estado.value = editBtn.dataset.estado || 'TRIAL';
        if (planId) planId.value = editBtn.dataset.planId || '';
        if (contratoPeriodo) contratoPeriodo.value = editBtn.dataset.periodo || 'MONTH';
        if (includedUsers) includedUsers.value = editBtn.dataset.includedUsers || '1';
        if (includedResources) includedResources.value = editBtn.dataset.includedResources || '2';
        if (userPassword) {
            userPassword.value = '';
            userPassword.type = 'password';
            userPassword.required = false;
            userPassword.placeholder = 'Dejar vacio para mantener';
        }
        if (userPasswordConfirm) {
            userPasswordConfirm.value = '';
            userPasswordConfirm.type = 'password';
            userPasswordConfirm.required = false;
            userPasswordConfirm.placeholder = 'Dejar vacio para mantener';
        }
        if (linkPath) {
            linkPath.value = (editBtn.dataset.link || '').replace(/^\/+/, '');
            linkPath.dataset.touched = '1';
            if (typeof Event === 'function') {
                linkPath.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
        if (title) title.textContent = 'Editar cliente';
        if (save) save.textContent = 'Guardar cambios';
        if (cancel) cancel.classList.remove('d-none');
        var modalEl = document.getElementById('nuevoClienteModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            var instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            instance.show();
        }
        return;
    }

    var configBtn = e.target.closest('.config-cliente-estados');
    if (configBtn) {
        var codigo = String(configBtn.dataset.codigo || '').trim();
        var razon = String(configBtn.dataset.razonSocial || '').trim();
        var codigoInput = document.getElementById('clienteEstadoCfgCodigo');
        var title = document.getElementById('clienteEstadoCfgTitle');
        if (codigoInput) codigoInput.value = codigo;
        if (title) title.textContent = codigo ? (codigo + ' - ' + (razon || '')) : '-';

        setClienteEstadoCfgForm(CLIENTE_ESTADO_CFG_DEFAULTS);
        loadClienteEstadoCfg(codigo).catch(function (err) {
            showClientesToast(err && err.message ? err.message : 'No se pudo cargar la configuracion del cliente.', 'danger');
        });
        updateEstadoPreview();

        var cfgModalEl = document.getElementById('clienteEstadoConfigModal');
        if (cfgModalEl && typeof bootstrap !== 'undefined') {
            var cfgModal = bootstrap.Modal.getInstance(cfgModalEl) || new bootstrap.Modal(cfgModalEl);
            cfgModal.show();
        }
        return;
    }

    var toggleBtn = e.target.closest('.toggle-cliente-status');
    if (toggleBtn) {
        toggleClienteStatusAjax(toggleBtn.dataset.id || '');
        return;
    }

    var row = e.target.closest('.cliente-row');
    if (row) {
        if (e.target.closest('a,button,.edit-cliente,.toggle-cliente-status')) return;
        clearClienteSelection();
        row.classList.add('table-active');
        showClienteDetailFromDataset({
            id: row.dataset.id || '',
            estado: row.dataset.estado || '',
            habilitado: row.dataset.habilitado || '0',
            nombreApellido: row.dataset.nombreApellido || '',
            telefono: row.dataset.telefono || '',
            dni: row.dataset.dni || '',
            localidad: row.dataset.localidad || '',
            plan: row.dataset.plan || '',
            periodo: row.dataset.periodo || '',
            estadoContrato: row.dataset.estadoContrato || '',
            contratoStart: row.dataset.contratoStart || '',
            contratoEnd: row.dataset.contratoEnd || '',
            precioTotal: row.dataset.precioTotal || ''
        });
        return;
    }

    if (e.target && e.target.id === 'clienteCfgResetBtn') {
        setClienteEstadoCfgForm(CLIENTE_ESTADO_CFG_DEFAULTS);
        updateEstadoPreview();
        return;
    }

    if (e.target && e.target.id === 'clienteCfgSaveBtn') {
        saveClienteEstadoCfg()
            .then(function () { showClientesToast('Configuracion guardada.', 'success'); })
            .catch(function (err) { showClientesToast(err && err.message ? err.message : 'No se pudo guardar la configuracion.', 'danger'); });
        return;
    }

    var togglePasswordBtn = e.target.closest('.toggle-password-btn');
    if (togglePasswordBtn) {
        var targetId = togglePasswordBtn.getAttribute('data-target') || '';
        var input = targetId ? document.getElementById(targetId) : null;
        if (!input) return;
        var isHidden = input.type === 'password';
        input.type = isHidden ? 'text' : 'password';
        var icon = togglePasswordBtn.querySelector('i');
        if (icon) {
            icon.classList.toggle('fa-eye', !isHidden);
            icon.classList.toggle('fa-eye-slash', isHidden);
        }
        return;
    }

    
});

document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'clienteCfgMsgGrace') {
        updateEstadoPreview();
    }
});
