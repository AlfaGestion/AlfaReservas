function cpEsc(v) {
    return String(v == null ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function cpToast(msg, type) {
    if (typeof showClientesToast === 'function') {
        showClientesToast(msg, type || 'info');
        return;
    }
    alert(msg);
}

function cpRenderUsers(users) {
    var tbody = document.getElementById('clientProfileUsersTableBody');
    if (!tbody) return;
    if (!users || !users.length) {
        tbody.innerHTML = '<tr><td colspan="3" class="text-muted text-center">Sin usuarios.</td></tr>';
        return;
    }
    tbody.innerHTML = users.map(function (u) {
        return '<tr>'
            + '<td>' + cpEsc(u.user || '-') + '</td>'
            + '<td>' + cpEsc(u.email || '-') + '</td>'
            + '<td>' + (Number(u.active || 0) === 1 ? 'Activo' : 'Inactivo') + '</td>'
            + '</tr>';
    }).join('');
}

function cpUpdateUsersQuota(quota) {
    var el = document.getElementById('cp_users_quota_text');
    if (!el) return;
    var total = Number((quota && quota.total != null) ? quota.total : (el.dataset.total || 0));
    var used = Number((quota && quota.used != null) ? quota.used : (el.dataset.used || 0));
    var remaining = Number((quota && quota.remaining != null) ? quota.remaining : Math.max(total - used, 0));
    el.dataset.total = String(total);
    el.dataset.used = String(used);
    el.dataset.remaining = String(remaining);
    el.innerHTML = 'Te quedan <strong>' + cpEsc(String(remaining)) + '</strong> usuario(s) disponibles (usados: '
        + cpEsc(String(used)) + ' / ' + cpEsc(String(total)) + ').';

    var addBtn = document.getElementById('cp_add_user_btn');
    var saveBtn = document.getElementById('saveClientProfileUserBtn');
    var canCreate = remaining > 0;
    if (addBtn) {
        addBtn.disabled = !canCreate;
        addBtn.classList.toggle('disabled', !canCreate);
        addBtn.setAttribute('aria-disabled', canCreate ? 'false' : 'true');
        if (!canCreate) {
            addBtn.removeAttribute('data-bs-toggle');
            addBtn.removeAttribute('data-bs-target');
            addBtn.title = 'No te quedan usuarios disponibles en tu plan.';
        } else {
            addBtn.setAttribute('data-bs-toggle', 'modal');
            addBtn.setAttribute('data-bs-target', '#clientProfileNewUserModal');
            addBtn.title = '';
        }
    }
    if (saveBtn) {
        saveBtn.disabled = !canCreate;
    }
}

function cpSetPlanSummary(planNombre, periodo, includedUsers, includedResources) {
    var planRow = document.querySelectorAll('#nav-customers .row.g-2 .col-md-3');
    if (!planRow || planRow.length < 4) return;
    planRow[0].innerHTML = '<strong>Plan:</strong> ' + cpEsc(planNombre || '-');
    planRow[1].innerHTML = '<strong>Periodo:</strong> ' + cpEsc(periodo === 'YEAR' ? 'Anual' : 'Mensual');
    planRow[2].innerHTML = '<strong>Usuarios:</strong> ' + cpEsc(String(includedUsers || '-'));
    planRow[3].innerHTML = '<strong>Recursos:</strong> ' + cpEsc(String(includedResources || '-'));
}

function cpMoney(v) {
    try {
        return Number(v || 0).toLocaleString('es-AR');
    } catch (_) {
        return String(v || '0');
    }
}

function cpGetSelectedPlanCard() {
    return document.querySelector('#cp_plan_cards .plan-card.active');
}

function cpRecalcPlanEstimate() {
    var card = cpGetSelectedPlanCard();
    var totalEl = document.getElementById('cp_plan_total');
    if (!card || !totalEl) return;
    var periodo = (document.getElementById('cp_plan_periodo') || {}).value || 'MONTH';
    var users = Number((document.getElementById('cp_plan_users') || {}).value || 0);
    var resources = Number((document.getElementById('cp_plan_resources') || {}).value || 0);
    var basePrice = Number(periodo === 'YEAR' ? (card.dataset.priceYear || 0) : (card.dataset.priceMonth || 0));
    var baseUsers = Number(card.dataset.users || 0);
    var baseResources = Number(card.dataset.resources || 0);
    var extraUsers = Math.max(users - baseUsers, 0);
    var extraResources = Math.max(resources - baseResources, 0);
    var estTotal = basePrice + (extraUsers * 1000) + (extraResources * 1200);
    totalEl.textContent = 'Total estimado: $' + cpMoney(estTotal) + (periodo === 'YEAR' ? ' / año' : ' / mes');
}

function cpRefreshLogoPreview() {
    var input = document.getElementById('cp_logo_file');
    if (!input || !input.files || !input.files.length) return;
    var wrap = document.getElementById('cp_logo_preview_wrap');
    if (!wrap) return;
    var img = document.getElementById('cp_logo_preview');
    var objectUrl = URL.createObjectURL(input.files[0]);
    if (!img) {
        wrap.innerHTML = '<img id="cp_logo_preview" alt="Logo cliente" style="max-height:80px;max-width:100%;">';
        img = document.getElementById('cp_logo_preview');
    }
    if (img) img.src = objectUrl;
}

document.addEventListener('click', async function (e) {
    var t = e.target ? (e.target.closest('button') || e.target) : null;
    if (!t) return;

    if (t.id === 'saveClientProfileBtn') {
        var id = (document.getElementById('cp_id') || {}).value || '';
        var razon = (document.getElementById('cp_razon_social') || {}).value || '';
        var contacto = (document.getElementById('cp_nombre_apellido') || {}).value || '';
        var fd = new FormData();
        fd.append('id', id);
        fd.append('razon_social', razon);
        fd.append('nombre_apellido', contacto);
        try {
            var res = await fetch(baseUrl + 'saveClientProfileAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            });
            var data = await res.json();
            if (!res.ok || data.error) return cpToast(data.message || 'No se pudo guardar perfil.', 'danger');
            cpToast(data.message || 'Perfil actualizado.', 'success');
        } catch (_) {
            cpToast('No se pudo guardar perfil.', 'danger');
        }
        return;
    }

    if (t.id === 'saveClientPlanBtn') {
        var idp = (document.getElementById('cp_id') || {}).value || '';
        var planId = (document.getElementById('cp_plan_id') || {}).value || '';
        var periodo = (document.getElementById('cp_plan_periodo') || {}).value || 'MONTH';
        var resources = (document.getElementById('cp_plan_resources') || {}).value || '0';
        var users = (document.getElementById('cp_plan_users') || {}).value || '0';
        if (!planId) return cpToast('Selecciona un plan.', 'danger');
        var fdp = new FormData();
        fdp.append('id', idp);
        fdp.append('plan_id', planId);
        fdp.append('periodo', periodo);
        fdp.append('included_resources', resources);
        fdp.append('included_users', users);
        try {
            var resp = await fetch(baseUrl + 'saveClientPlanAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fdp
            });
            var datap = await resp.json();
            if (!resp.ok || datap.error) return cpToast(datap.message || 'No se pudo guardar el plan.', 'danger');
            var d = datap.data || {};
            cpSetPlanSummary(d.planNombre || '', d.periodo || periodo, d.includedUsers || '-', d.includedResources || '-');
            cpToast(datap.message || 'Plan actualizado.', 'success');
        } catch (_) {
            cpToast('No se pudo guardar el plan.', 'danger');
        }
        return;
    }

    if (t.id === 'saveOwnPasswordBtn') {
        var curr = (document.getElementById('cp_current_password') || {}).value || '';
        var np = (document.getElementById('cp_new_password_self') || {}).value || '';
        var nr = (document.getElementById('cp_new_repeat_password_self') || {}).value || '';
        var rule2 = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
        if (!rule2.test(np)) return cpToast('La contrasena debe tener al menos una mayuscula, una minuscula y un numero.', 'danger');
        var fdx = new FormData();
        fdx.append('current_password', curr);
        fdx.append('new_password', np);
        fdx.append('repeat_password', nr);
        try {
            var rx = await fetch(baseUrl + 'saveOwnClientPasswordAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fdx
            });
            var dx = await rx.json();
            if (!rx.ok || dx.error) return cpToast(dx.message || 'No se pudo cambiar la contrasena.', 'danger');
            (document.getElementById('cp_current_password') || {}).value = '';
            (document.getElementById('cp_new_password_self') || {}).value = '';
            (document.getElementById('cp_new_repeat_password_self') || {}).value = '';
            cpToast(dx.message || 'Contrasena actualizada.', 'success');
        } catch (_) {
            cpToast('No se pudo cambiar la contrasena.', 'danger');
        }
        return;
    }

    if (t.id === 'saveClientLogoBtn') {
        var id2 = (document.getElementById('cp_id') || {}).value || '';
        var fileInput = document.getElementById('cp_logo_file');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            return cpToast('Selecciona un logo.', 'danger');
        }
        var fd2 = new FormData();
        fd2.append('id', id2);
        fd2.append('logo', fileInput.files[0]);
        try {
            var res2 = await fetch(baseUrl + 'saveClientLogoAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd2
            });
            var data2 = await res2.json();
            if (!res2.ok || data2.error) return cpToast(data2.message || 'No se pudo guardar logo.', 'danger');
            cpToast(data2.message || 'Logo actualizado.', 'success');
            cpRefreshLogoPreview();
            fileInput.value = '';
        } catch (_) {
            cpToast('No se pudo guardar logo.', 'danger');
        }
        return;
    }

    if (t.id === 'saveClientProfileUserBtn') {
        var quotaEl = document.getElementById('cp_users_quota_text');
        var remaining = Number((quotaEl && quotaEl.dataset && quotaEl.dataset.remaining) ? quotaEl.dataset.remaining : 0);
        if (remaining <= 0) return cpToast('No te quedan usuarios disponibles en tu plan.', 'danger');

        var p = (document.getElementById('cp_new_password') || {}).value || '';
        var rule = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
        if (!rule.test(p)) return cpToast('La contrasena debe tener al menos una mayuscula, una minuscula y un numero.', 'danger');

        var payload = new URLSearchParams();
        payload.append('user', (document.getElementById('cp_new_user') || {}).value || '');
        payload.append('email', (document.getElementById('cp_new_email') || {}).value || '');
        payload.append('password', p);
        payload.append('repeat_password', (document.getElementById('cp_new_repeat_password') || {}).value || '');

        try {
            var res3 = await fetch(baseUrl + 'addClientBaseUserAjax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: payload.toString()
            });
            var data3 = await res3.json();
            if (!res3.ok || data3.error) return cpToast(data3.message || 'No se pudo crear usuario.', 'danger');
            if (data3.data && Array.isArray(data3.data.users)) cpRenderUsers(data3.data.users);
            if (data3.data && data3.data.quota) cpUpdateUsersQuota(data3.data.quota);
            var modalEl = document.getElementById('clientProfileNewUserModal');
            if (modalEl && window.bootstrap && bootstrap.Modal) {
                var mi = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                mi.hide();
            }
            cpToast(data3.message || 'Usuario creado.', 'success');
        } catch (_) {
            cpToast('No se pudo crear usuario.', 'danger');
        }
    }
});

document.addEventListener('click', function (e) {
    var card = e.target && e.target.closest ? e.target.closest('.plan-card') : null;
    if (!card) return;
    var cards = document.querySelectorAll('#cp_plan_cards .plan-card');
    cards.forEach(function (c) { c.classList.remove('active'); });
    card.classList.add('active');
    var pid = card.dataset.planId || '';
    var hiddenPlan = document.getElementById('cp_plan_id');
    var users = document.getElementById('cp_plan_users');
    var resources = document.getElementById('cp_plan_resources');
    if (hiddenPlan) hiddenPlan.value = pid;
    if (users) users.value = card.dataset.users || '1';
    if (resources) resources.value = card.dataset.resources || '2';
    cpRecalcPlanEstimate();
});

document.addEventListener('input', function (e) {
    if (!e.target) return;
    if (e.target.id === 'cp_plan_periodo' || e.target.id === 'cp_plan_users' || e.target.id === 'cp_plan_resources') {
        cpRecalcPlanEstimate();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    var profileForm = document.getElementById('clientProfileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (e) {
            e.preventDefault();
        });
    }
    cpUpdateUsersQuota(null);
    cpRecalcPlanEstimate();
});
