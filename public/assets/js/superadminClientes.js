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

function valueOrDash(v) {
    var t = String(v == null ? '' : v).trim();
    return t === '' ? '-' : t;
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
        var link = cliente.link ? '<a href="' + escHtml(cliente.link) + '" target="_blank">' + escHtml(cliente.link) + '</a>' : '-';
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
            ' data-periodo="' + escHtml(cliente.contrato_periodo || '') + '"' +
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
            '<td><div class="d-flex gap-2">' +
            '<button type="button" class="btn btn-sm btn-outline-primary edit-cliente"' +
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
            '>Editar</button>' +
            (habilitado
                ? '<button type="button" class="btn btn-sm btn-outline-warning toggle-cliente-status" data-id="' + escHtml(cliente.id) + '">Deshabilitar</button>'
                : '<button type="button" class="btn btn-sm btn-outline-success toggle-cliente-status" data-id="' + escHtml(cliente.id) + '">Habilitar</button>') +
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
    if (id) id.value = '';
    if (save) save.textContent = 'Guardar';
    if (cancel) cancel.classList.add('d-none');
    if (title) title.textContent = 'Nuevo cliente';
    if (codigo && nextCode) codigo.value = nextCode;
    if (estado) estado.value = 'TRIAL';
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
    fetch(baseUrl + 'saveClienteAjax', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, json: j }; }); })
        .then(function (result) {
            if (!result.ok || result.json.error) return alert(result.json.message || 'No se pudo guardar el cliente.');
            var data = result.json.data || {};
            renderClientesTable(data.clientes || []);
            resetClienteForm(data.nextClienteCodigo || '');
            var modalEl = document.getElementById('nuevoClienteModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                var instance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                instance.hide();
            }
            alert('Cliente guardado correctamente.');
        }).catch(function () { alert('No se pudo guardar el cliente.'); });
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
            if (!result.ok || result.json.error) return alert(result.json.message || 'No se pudo actualizar el estado.');
            var data = result.json.data || {};
            renderClientesTable(data.clientes || []);
            alert(result.json.message || 'Estado actualizado.');
        }).catch(function () { alert('No se pudo actualizar el estado.'); });
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
    }
});
