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

function cpRefreshLogoPreview(inputId, wrapId, imageId) {
    var input = document.getElementById(inputId || 'cp_logo_file');
    if (!input || !input.files || !input.files.length) return;
    var wrap = document.getElementById(wrapId || 'cp_logo_preview_wrap');
    if (!wrap) return;
    var img = document.getElementById(imageId || 'cp_logo_preview');
    var objectUrl = URL.createObjectURL(input.files[0]);
    if (!img) {
        wrap.innerHTML = '<img id="' + cpEsc(imageId || 'cp_logo_preview') + '" alt="Logo cliente" style="max-height:80px;max-width:100%;">';
        img = document.getElementById(imageId || 'cp_logo_preview');
    }
    if (img) img.src = objectUrl;
}

function cpSyncAdvancedScheduleRow(row) {
    if (!row) return;
    var activeInput = row.querySelector('.cp-advanced-active');
    var morningEnabled = row.querySelector('.cp-advanced-morning-enabled');
    var afternoonEnabled = row.querySelector('.cp-advanced-afternoon-enabled');
    var morningSlot = row.querySelector('[data-slot="morning"]');
    var afternoonSlot = row.querySelector('[data-slot="afternoon"]');
    var isActive = !!(activeInput && activeInput.checked);

    if (morningEnabled) {
        morningEnabled.disabled = !isActive;
        if (!isActive) morningEnabled.checked = false;
    }
    if (afternoonEnabled) {
        afternoonEnabled.disabled = !isActive;
        if (!isActive) afternoonEnabled.checked = false;
    }
    if (morningSlot) {
        morningSlot.classList.toggle('disabled', !isActive || !(morningEnabled && morningEnabled.checked));
        Array.prototype.slice.call(morningSlot.querySelectorAll('select')).forEach(function (el) {
            el.disabled = !isActive || !(morningEnabled && morningEnabled.checked);
        });
    }
    if (afternoonSlot) {
        afternoonSlot.classList.toggle('disabled', !isActive || !(afternoonEnabled && afternoonEnabled.checked));
        Array.prototype.slice.call(afternoonSlot.querySelectorAll('select')).forEach(function (el) {
            el.disabled = !isActive || !(afternoonEnabled && afternoonEnabled.checked);
        });
    }
}

function cpCollectAdvancedSchedule() {
    var payload = {};
    Array.prototype.slice.call(document.querySelectorAll('[data-advanced-day]')).forEach(function (row) {
        var day = row.getAttribute('data-advanced-day') || '';
        if (!day) return;
        payload[day] = {
            active: !!((row.querySelector('.cp-advanced-active') || {}).checked),
            morning_enabled: !!((row.querySelector('.cp-advanced-morning-enabled') || {}).checked),
            morning_from: (row.querySelector('.cp-advanced-morning-from') || {}).value || '',
            morning_until: (row.querySelector('.cp-advanced-morning-until') || {}).value || '',
            afternoon_enabled: !!((row.querySelector('.cp-advanced-afternoon-enabled') || {}).checked),
            afternoon_from: (row.querySelector('.cp-advanced-afternoon-from') || {}).value || '',
            afternoon_until: (row.querySelector('.cp-advanced-afternoon-until') || {}).value || ''
        };
    });
    return payload;
}

function cpSyncAdvancedWithTopDays(scopeRoot) {
    var root = scopeRoot || document;
    var selectedDays = {};
    Array.prototype.slice.call(root.querySelectorAll('input[name="cp_open_days"]:checked')).forEach(function (node) {
        selectedDays[String(node.value || '')] = true;
    });

    Array.prototype.slice.call(root.querySelectorAll('[data-advanced-day]')).forEach(function (row) {
        var day = String(row.getAttribute('data-advanced-day') || '');
        var activeInput = row.querySelector('.cp-advanced-active');
        if (!activeInput) return;

        if (selectedDays[day]) {
            if (!activeInput.checked) {
                activeInput.checked = true;
            }
        } else {
            activeInput.checked = false;
            var morningEnabled = row.querySelector('.cp-advanced-morning-enabled');
            var afternoonEnabled = row.querySelector('.cp-advanced-afternoon-enabled');
            if (morningEnabled) morningEnabled.checked = false;
            if (afternoonEnabled) afternoonEnabled.checked = false;
        }

        cpSyncAdvancedScheduleRow(row);
    });
}

function cpApplyAdvancedGlobal(box, action) {
    if (!box) return;
    var morningFrom = (box.querySelector('[data-advanced-global="morning-from"]') || {}).value || '';
    var morningUntil = (box.querySelector('[data-advanced-global="morning-until"]') || {}).value || '';
    var afternoonFrom = (box.querySelector('[data-advanced-global="afternoon-from"]') || {}).value || '';
    var afternoonUntil = (box.querySelector('[data-advanced-global="afternoon-until"]') || {}).value || '';
    var morningEnabledAll = !!((box.querySelector('[data-advanced-global="morning-enabled-all"]') || {}).checked);
    var afternoonEnabledAll = !!((box.querySelector('[data-advanced-global="afternoon-enabled-all"]') || {}).checked);

    Array.prototype.slice.call(box.querySelectorAll('[data-advanced-day]')).forEach(function (row) {
        var activeInput = row.querySelector('.cp-advanced-active');
        var morningEnabled = row.querySelector('.cp-advanced-morning-enabled');
        var afternoonEnabled = row.querySelector('.cp-advanced-afternoon-enabled');
        var morningFromEl = row.querySelector('.cp-advanced-morning-from');
        var morningUntilEl = row.querySelector('.cp-advanced-morning-until');
        var afternoonFromEl = row.querySelector('.cp-advanced-afternoon-from');
        var afternoonUntilEl = row.querySelector('.cp-advanced-afternoon-until');

        if (action === 'check-all') {
            if (activeInput) activeInput.checked = true;
            if (morningEnabled) morningEnabled.checked = true;
            if (afternoonEnabled) afternoonEnabled.checked = true;
        }
        if (action === 'uncheck-all') {
            if (activeInput) activeInput.checked = false;
            if (morningEnabled) morningEnabled.checked = false;
            if (afternoonEnabled) afternoonEnabled.checked = false;
        }
        if (!action) {
            if (activeInput) {
                activeInput.checked = morningEnabledAll || afternoonEnabledAll;
            }
            if (morningEnabled) {
                morningEnabled.checked = morningEnabledAll && !!(activeInput && activeInput.checked);
            }
            if (afternoonEnabled) {
                afternoonEnabled.checked = afternoonEnabledAll && !!(activeInput && activeInput.checked);
            }
        }

        if (morningFromEl && morningFrom) morningFromEl.value = morningFrom;
        if (morningUntilEl && morningUntil) morningUntilEl.value = morningUntil;
        if (afternoonFromEl && afternoonFrom) afternoonFromEl.value = afternoonFrom;
        if (afternoonUntilEl && afternoonUntil) afternoonUntilEl.value = afternoonUntil;

        cpSyncAdvancedScheduleRow(row);
    });
}

document.addEventListener('click', async function (e) {
    var t = e.target ? (e.target.closest('button') || e.target) : null;
    if (!t) return;

    var dayChip = e.target && e.target.closest ? e.target.closest('.cp-setup-day, .cp-websetup-day') : null;
    if (dayChip && !dayChip.closest('button')) {
        var dayInput = dayChip.querySelector('input[type="checkbox"]');
        if (dayInput && e.target !== dayInput) {
            e.preventDefault();
            dayInput.checked = !dayInput.checked;
        }
        return;
    }

    var advancedToggle = e.target && e.target.closest ? e.target.closest('[data-advanced-toggle]') : null;
    if (advancedToggle) {
        var advancedBox = advancedToggle.closest('.cp-advanced-schedule, .cp-websetup-advanced');
        var advancedBody = advancedBox ? advancedBox.querySelector('[data-advanced-body]') : null;
        if (advancedBody) {
            var nextOpen = !advancedBody.classList.contains('is-open');
            advancedBody.classList.toggle('is-open', nextOpen);
            advancedToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        }
        return;
    }

    var advancedAction = e.target && e.target.closest ? e.target.closest('[data-advanced-action]') : null;
    if (advancedAction) {
        var advancedActionBox = advancedAction.closest('.cp-advanced-schedule, .cp-websetup-advanced');
        cpApplyAdvancedGlobal(advancedActionBox, advancedAction.getAttribute('data-advanced-action') || '');
        return;
    }

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
            cpRefreshLogoPreview('cp_logo_file', 'cp_logo_preview_wrap', 'cp_logo_preview');
            fileInput.value = '';
        } catch (_) {
            cpToast('No se pudo guardar logo.', 'danger');
        }
        return;
    }

    if (t.id === 'saveClientSetupLogoBtn') {
        var id2b = (document.getElementById('cp_id') || {}).value || '';
        var fileInput2 = document.getElementById('cp_setup_logo_file');
        if (!fileInput2 || !fileInput2.files || !fileInput2.files.length) {
            return cpToast('Selecciona un logo.', 'danger');
        }
        var fd2b = new FormData();
        fd2b.append('id', id2b);
        fd2b.append('logo', fileInput2.files[0]);
        try {
            var res2b = await fetch(baseUrl + 'saveClientLogoAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fd2b
            });
            var data2b = await res2b.json();
            if (!res2b.ok || data2b.error) return cpToast(data2b.message || 'No se pudo guardar logo.', 'danger');
            cpToast(data2b.message || 'Logo actualizado.', 'success');
            cpRefreshLogoPreview('cp_setup_logo_file', 'cp_setup_logo_preview_wrap', 'cp_setup_logo_preview');
            fileInput2.value = '';
        } catch (_) {
            cpToast('No se pudo guardar logo.', 'danger');
        }
        return;
    }

    if (t.id === 'saveClientSetupBtn') {
        var idSetup = (document.getElementById('cp_id') || {}).value || '';
        var dismissCheckbox = document.getElementById('cp_setup_dismiss');
        var fdSetup = new FormData();
        fdSetup.append('id', idSetup);
        fdSetup.append('site_title', (document.getElementById('cp_site_title') || {}).value || '');
        fdSetup.append('service_name', (document.getElementById('cp_service_name') || {}).value || '');
        fdSetup.append('reservation_email', (document.getElementById('cp_reservation_email') || {}).value || '');
        fdSetup.append('open_from', (document.getElementById('cp_open_from') || {}).value || '');
        fdSetup.append('open_until', (document.getElementById('cp_open_until') || {}).value || '');
        fdSetup.append('primary_color', (document.getElementById('cp_primary_color') || {}).value || '');
        fdSetup.append('accent_color', (document.getElementById('cp_accent_color') || {}).value || '');
        fdSetup.append('advanced_schedule_json', JSON.stringify(cpCollectAdvancedSchedule()));
        fdSetup.append('dismiss_prompt', dismissCheckbox && dismissCheckbox.checked ? '1' : '0');
        Array.prototype.slice.call(document.querySelectorAll('input[name="cp_open_days"]:checked')).forEach(function (node) {
            fdSetup.append('open_days[]', node.value || '');
        });
        try {
            var resSetup = await fetch(baseUrl + 'saveClientSetupAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fdSetup
            });
            var dataSetup = await resSetup.json();
            if (!resSetup.ok || dataSetup.error) return cpToast(dataSetup.message || 'No se pudo guardar la configuracion.', 'danger');
            cpToast(dataSetup.message || 'Configuracion actualizada.', 'success');
            if (dismissCheckbox && dismissCheckbox.checked) {
                var currentFrame = t.closest('.cp-setup-frame, .cp-websetup-frame');
                if (currentFrame) {
                    currentFrame.style.display = 'none';
                }
            }
        } catch (_) {
            cpToast('No se pudo guardar la configuracion.', 'danger');
        }
        return;
    }

    if (t.id === 'dismissClientSetupBtn') {
        var idDismiss = (document.getElementById('cp_id') || {}).value || '';
        var dismissCheckboxOnly = document.getElementById('cp_setup_dismiss');
        if (!dismissCheckboxOnly || !dismissCheckboxOnly.checked) {
            return cpToast('Tilda "No volver a mostrar" antes de ocultar el recordatorio.', 'danger');
        }
        var fdDismiss = new FormData();
        fdDismiss.append('id', idDismiss);
        fdDismiss.append('dismiss_only', '1');
        fdDismiss.append('dismiss_prompt', '1');
        try {
            var resDismiss = await fetch(baseUrl + 'saveClientSetupAjax', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: fdDismiss
            });
            var dataDismiss = await resDismiss.json();
            if (!resDismiss.ok || dataDismiss.error) return cpToast(dataDismiss.message || 'No se pudo ocultar el recordatorio.', 'danger');
            cpToast(dataDismiss.message || 'Recordatorio oculto.', 'success');
            var setupFrame = t.closest('.cp-setup-frame, .cp-websetup-frame');
            if (setupFrame) {
                setupFrame.style.display = 'none';
            }
        } catch (_) {
            cpToast('No se pudo ocultar el recordatorio.', 'danger');
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

document.addEventListener('change', function (e) {
    if (!e.target) return;
    if (e.target.id === 'cp_logo_file') {
        cpRefreshLogoPreview('cp_logo_file', 'cp_logo_preview_wrap', 'cp_logo_preview');
    }
    if (e.target.id === 'cp_setup_logo_file') {
        cpRefreshLogoPreview('cp_setup_logo_file', 'cp_setup_logo_preview_wrap', 'cp_setup_logo_preview');
    }
    var advancedRow = e.target.closest ? e.target.closest('[data-advanced-day]') : null;
    if (advancedRow) {
        cpSyncAdvancedScheduleRow(advancedRow);
    }
    if (e.target.name === 'cp_open_days') {
        var scopeRoot = e.target.closest ? (e.target.closest('.cp-setup-frame, .cp-websetup-frame') || document) : document;
        cpSyncAdvancedWithTopDays(scopeRoot);
    }
    var advancedBox = e.target.closest ? e.target.closest('.cp-advanced-schedule, .cp-websetup-advanced') : null;
    if (advancedBox && e.target.matches && e.target.matches('[data-advanced-global]')) {
        cpApplyAdvancedGlobal(advancedBox, '');
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
    Array.prototype.slice.call(document.querySelectorAll('[data-advanced-day]')).forEach(cpSyncAdvancedScheduleRow);
    Array.prototype.slice.call(document.querySelectorAll('.cp-setup-frame, .cp-websetup-frame')).forEach(cpSyncAdvancedWithTopDays);
    Array.prototype.slice.call(document.querySelectorAll('.cp-advanced-schedule, .cp-websetup-advanced')).forEach(function (box) {
        var body = box.querySelector('[data-advanced-body]');
        var toggle = box.querySelector('[data-advanced-toggle]');
        if (!body || !toggle) return;
        body.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    });
});
