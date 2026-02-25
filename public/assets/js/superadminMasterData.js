function v(obj, key, def) {
    if (obj && Object.prototype.hasOwnProperty.call(obj, key) && obj[key] !== null && obj[key] !== undefined) {
        return obj[key];
    }
    return def;
}

function renderPlanesTable(planes) {
    var tbody = document.getElementById('planesTableBody');
    if (!tbody) return;
    if (!planes || planes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">No hay planes cargados.</td></tr>';
        return;
    }
    tbody.innerHTML = planes.map(function (plan) {
        return '<tr>' +
            '<td class="d-none">' + v(plan, 'id', '') + '</td>' +
            '<td>' + v(plan, 'codigo', '') + '</td>' +
            '<td>' + v(plan, 'nombre', '') + '</td>' +
            '<td>' + v(plan, 'price_month', '0') + '</td>' +
            '<td>' + v(plan, 'price_year', '0') + '</td>' +
            '<td>' + (Number(v(plan, 'email_por_reserva', 0)) === 1 ? 'Si' : 'No') + '</td>' +
            '<td>' + (Number(v(plan, 'activo', 0)) === 1 ? 'Si' : 'No') + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-primary edit-plan"' +
            ' data-id="' + v(plan, 'id', '') + '"' +
            ' data-codigo="' + v(plan, 'codigo', '') + '"' +
            ' data-nombre="' + v(plan, 'nombre', '') + '"' +
            ' data-price-month="' + v(plan, 'price_month', '0') + '"' +
            ' data-price-year="' + v(plan, 'price_year', '0') + '"' +
            ' data-included-users="' + v(plan, 'included_users', '1') + '"' +
            ' data-included-resources="' + v(plan, 'included_resources', '2') + '"' +
            ' data-max-users="' + v(plan, 'max_users', '50') + '"' +
            ' data-max-resources="' + v(plan, 'max_resources', '100') + '"' +
            ' data-soporte-horas="' + v(plan, 'soporte_horas', '0') + '"' +
            ' data-email-por-reserva="' + Number(v(plan, 'email_por_reserva', 0)) + '">Editar</button></td>' +
            '</tr>';
    }).join('');
}

function resetPlanForm() {
    var form = document.getElementById('planForm');
    if (!form) return;
    form.reset();
    var id = document.getElementById('plan_id');
    var save = document.getElementById('planSaveBtn');
    var cancel = document.getElementById('planCancelEditBtn');
    if (id) id.value = '';
    if (save) save.textContent = 'Guardar plan';
    if (cancel) cancel.classList.add('d-none');
}

function renderRubrosTable(rubros) {
    var tbody = document.getElementById('rubrosTableBody');
    if (!tbody) return;
    if (!rubros || rubros.length === 0) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-muted text-center">No hay rubros cargados.</td></tr>';
        return;
    }
    tbody.innerHTML = rubros.map(function (rubro) {
        return '<tr>' +
            '<td>' + v(rubro, 'id', '') + '</td>' +
            '<td>' + v(rubro, 'descripcion', '') + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-primary edit-rubro" data-id="' + v(rubro, 'id', '') + '" data-descripcion="' + v(rubro, 'descripcion', '') + '">Editar</button></td>' +
            '</tr>';
    }).join('');

    var selectRubro = document.getElementById('param_rubro_id');
    if (selectRubro) {
        var current = selectRubro.value;
        selectRubro.innerHTML = '<option value="">Seleccionar rubro</option>' + rubros.map(function (rubro) {
            return '<option value="' + v(rubro, 'id', '') + '">' + v(rubro, 'descripcion', '') + '</option>';
        }).join('');
        if (current) selectRubro.value = current;
    }
}

function resetRubroForm() {
    var form = document.getElementById('rubroForm');
    if (!form) return;
    form.reset();
    var id = document.getElementById('rubro_id');
    var save = document.getElementById('rubroSaveBtn');
    var cancel = document.getElementById('rubroCancelEditBtn');
    if (id) id.value = '';
    if (save) save.textContent = 'Guardar rubro';
    if (cancel) cancel.classList.add('d-none');
}

function renderRubroParametrosTable(params) {
    var tbody = document.getElementById('rubroParametrosTableBody');
    if (!tbody) return;
    if (!params || params.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-muted text-center">No hay parametros cargados.</td></tr>';
        return;
    }
    tbody.innerHTML = params.map(function (param) {
        return '<tr>' +
            '<td>' + v(param, 'rubro_descripcion', '') + '</td>' +
            '<td>' + v(param, 'key', '') + '</td>' +
            '<td>' + v(param, 'label', '') + '</td>' +
            '<td>' + v(param, 'precio_por_unidad', '0') + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-primary edit-rubro-parametro"' +
            ' data-id="' + v(param, 'id', '') + '"' +
            ' data-rubro-id="' + v(param, 'rubro_id', '') + '"' +
            ' data-key="' + v(param, 'key', '') + '"' +
            ' data-label="' + v(param, 'label', '') + '"' +
            ' data-min-value="' + v(param, 'min_value', '1') + '"' +
            ' data-max-value="' + v(param, 'max_value', '999') + '"' +
            ' data-precio-por-unidad="' + v(param, 'precio_por_unidad', '0') + '">Editar</button></td>' +
            '</tr>';
    }).join('');
}

function resetRubroParametroForm() {
    var form = document.getElementById('rubroParametroForm');
    if (!form) return;
    form.reset();
    var id = document.getElementById('param_id');
    var save = document.getElementById('rubroParametroSaveBtn');
    var cancel = document.getElementById('rubroParametroCancelEditBtn');
    if (id) id.value = '';
    if (save) save.textContent = 'Guardar parametro';
    if (cancel) cancel.classList.add('d-none');
}

function safeJsonData(json, key) {
    if (json && json.data && json.data[key]) return json.data[key];
    return [];
}

function savePlanAjax() {
    var form = document.getElementById('planForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;
    fetch(baseUrl + 'savePlanAjax', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, json: j }; }); })
        .then(function (result) {
            if (!result.ok || result.json.error) return alert(result.json.message || 'No se pudo guardar el plan.');
            renderPlanesTable(safeJsonData(result.json, 'planes'));
            resetPlanForm();
            alert('Plan guardado correctamente.');
        }).catch(function () { alert('No se pudo guardar el plan.'); });
}

function saveRubroAjax() {
    var form = document.getElementById('rubroForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;
    fetch(baseUrl + 'saveRubroAjax', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, json: j }; }); })
        .then(function (result) {
            if (!result.ok || result.json.error) return alert(result.json.message || 'No se pudo guardar el rubro.');
            renderRubrosTable(safeJsonData(result.json, 'rubros'));
            resetRubroForm();
            alert('Rubro guardado correctamente.');
        }).catch(function () { alert('No se pudo guardar el rubro.'); });
}

function saveRubroParametroAjax() {
    var form = document.getElementById('rubroParametroForm');
    if (!form) return;
    if (typeof form.reportValidity === 'function' && !form.reportValidity()) return;
    fetch(baseUrl + 'saveRubroParametroAjax', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form)
    }).then(function (res) { return res.json().then(function (j) { return { ok: res.ok, json: j }; }); })
        .then(function (result) {
            if (!result.ok || result.json.error) return alert(result.json.message || 'No se pudo guardar el parametro.');
            renderRubroParametrosTable(safeJsonData(result.json, 'rubroParametros'));
            resetRubroParametroForm();
            alert('Parametro guardado correctamente.');
        }).catch(function () { alert('No se pudo guardar el parametro.'); });
}

document.addEventListener('submit', function (e) {
    var target = e.target;
    if (!target) return;

    if (target.id === 'planForm' || target.id === 'rubroForm' || target.id === 'rubroParametroForm') {
        e.preventDefault();
    }

    if (target.id === 'planForm') {
        savePlanAjax();
    }

    if (target.id === 'rubroForm') {
        saveRubroAjax();
    }

    if (target.id === 'rubroParametroForm') {
        saveRubroParametroAjax();
    }
});

document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'planSaveBtn') {
        savePlanAjax();
        return;
    }
    if (e.target && e.target.id === 'rubroSaveBtn') {
        saveRubroAjax();
        return;
    }
    if (e.target && e.target.id === 'rubroParametroSaveBtn') {
        saveRubroParametroAjax();
        return;
    }

    var editPlanBtn = e.target.closest('.edit-plan');
    if (editPlanBtn) {
        var id = document.getElementById('plan_id');
        var codigo = document.getElementById('plan_codigo');
        var nombre = document.getElementById('plan_nombre');
        var month = document.getElementById('plan_price_month');
        var year = document.getElementById('plan_price_year');
        var includedUsers = document.getElementById('plan_included_users');
        var includedResources = document.getElementById('plan_included_resources');
        var maxUsers = document.getElementById('plan_max_users');
        var maxResources = document.getElementById('plan_max_resources');
        var soporte = document.getElementById('plan_soporte_horas');
        var emailPorReserva = document.getElementById('plan_email_por_reserva');
        var save = document.getElementById('planSaveBtn');
        var cancel = document.getElementById('planCancelEditBtn');
        if (id) id.value = editPlanBtn.dataset.id || '';
        if (codigo) codigo.value = editPlanBtn.dataset.codigo || '';
        if (nombre) nombre.value = editPlanBtn.dataset.nombre || '';
        if (month) month.value = editPlanBtn.dataset.priceMonth || '0';
        if (year) year.value = editPlanBtn.dataset.priceYear || '0';
        if (includedUsers) includedUsers.value = editPlanBtn.dataset.includedUsers || '1';
        if (includedResources) includedResources.value = editPlanBtn.dataset.includedResources || '2';
        if (maxUsers) maxUsers.value = editPlanBtn.dataset.maxUsers || '50';
        if (maxResources) maxResources.value = editPlanBtn.dataset.maxResources || '100';
        if (soporte) soporte.value = editPlanBtn.dataset.soporteHoras || '0';
        if (emailPorReserva) emailPorReserva.value = editPlanBtn.dataset.emailPorReserva || '0';
        if (save) save.textContent = 'Guardar cambios';
        if (cancel) cancel.classList.remove('d-none');
        return;
    }
    if (e.target.id === 'planCancelEditBtn') return resetPlanForm();

    var editRubroBtn = e.target.closest('.edit-rubro');
    if (editRubroBtn) {
        var rubroId = document.getElementById('rubro_id');
        var descripcion = document.getElementById('descripcion');
        var rubroSave = document.getElementById('rubroSaveBtn');
        var rubroCancel = document.getElementById('rubroCancelEditBtn');
        if (rubroId) rubroId.value = editRubroBtn.dataset.id || '';
        if (descripcion) descripcion.value = editRubroBtn.dataset.descripcion || '';
        if (rubroSave) rubroSave.textContent = 'Guardar cambios';
        if (rubroCancel) rubroCancel.classList.remove('d-none');
        return;
    }
    if (e.target.id === 'rubroCancelEditBtn') return resetRubroForm();

    var editParamBtn = e.target.closest('.edit-rubro-parametro');
    if (editParamBtn) {
        var paramId = document.getElementById('param_id');
        var paramRubroId = document.getElementById('param_rubro_id');
        var key = document.getElementById('param_key');
        var label = document.getElementById('param_label');
        var min = document.getElementById('param_min');
        var max = document.getElementById('param_max');
        var precio = document.getElementById('param_precio');
        var paramSave = document.getElementById('rubroParametroSaveBtn');
        var paramCancel = document.getElementById('rubroParametroCancelEditBtn');
        if (paramId) paramId.value = editParamBtn.dataset.id || '';
        if (paramRubroId) paramRubroId.value = editParamBtn.dataset.rubroId || '';
        if (key) key.value = editParamBtn.dataset.key || '';
        if (label) label.value = editParamBtn.dataset.label || '';
        if (min) min.value = editParamBtn.dataset.minValue || '1';
        if (max) max.value = editParamBtn.dataset.maxValue || '999';
        if (precio) precio.value = editParamBtn.dataset.precioPorUnidad || '0';
        if (paramSave) paramSave.textContent = 'Guardar cambios';
        if (paramCancel) paramCancel.classList.remove('d-none');
        return;
    }
    if (e.target.id === 'rubroParametroCancelEditBtn') return resetRubroParametroForm();
});
