var CLIENTE_ESTADO_DEFAULTS = {
    trial_days: 15,
    grace_days: 5,
    read_only_days: 10,
    msg_trial: 'Periodo de prueba activo. Te quedan <dias_restantes> dia(s). Vence el <fecha_fin>.',
    msg_grace: 'Estas en periodo de gracia. Te quedan <dias_restantes> dia(s) para regularizar el plan.',
    msg_read_only: 'Modo solo lectura activo. Te quedan <dias_restantes> dia(s) antes de la suspension.',
    msg_suspended: 'Tu cuenta esta suspendida por falta de pago. Contacta al administrador para reactivarla.'
};

function cfgEl(id) {
    return document.getElementById(id);
}

function setClienteEstadoForm(values) {
    var v = values || CLIENTE_ESTADO_DEFAULTS;
    if (cfgEl('cfg_trial_days')) cfgEl('cfg_trial_days').value = v.trial_days;
    if (cfgEl('cfg_grace_days')) cfgEl('cfg_grace_days').value = v.grace_days;
    if (cfgEl('cfg_read_only_days')) cfgEl('cfg_read_only_days').value = v.read_only_days;
    if (cfgEl('cfg_msg_trial')) cfgEl('cfg_msg_trial').value = v.msg_trial || '';
    if (cfgEl('cfg_msg_grace')) cfgEl('cfg_msg_grace').value = v.msg_grace || '';
    if (cfgEl('cfg_msg_read_only')) cfgEl('cfg_msg_read_only').value = v.msg_read_only || '';
    if (cfgEl('cfg_msg_suspended')) cfgEl('cfg_msg_suspended').value = v.msg_suspended || '';
}

async function loadClienteEstadoConfig(clienteCodigo) {
    if (!clienteCodigo) {
        if (cfgEl('estadoConfigForm')) cfgEl('estadoConfigForm').classList.add('d-none');
        return;
    }

    try {
        var response = await fetch(baseUrl + 'getClienteEstadoConfigAjax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ clienteCodigo: clienteCodigo })
        });
        var responseData = await response.json();
        if (!response.ok || responseData.error) {
            alert(responseData.message || 'No se pudo cargar la configuracion del cliente.');
            return;
        }

        if (cfgEl('estadoConfigForm')) cfgEl('estadoConfigForm').classList.remove('d-none');
        setClienteEstadoForm((responseData.data && responseData.data.config) ? responseData.data.config : CLIENTE_ESTADO_DEFAULTS);
    } catch (error) {
        alert('No se pudo cargar la configuracion del cliente.');
    }
}

async function saveClienteEstadoConfig(clienteCodigo) {
    if (!clienteCodigo) {
        alert('Selecciona un cliente.');
        return;
    }

    var payload = {
        clienteCodigo: String(clienteCodigo || ''),
        trial_days: Number((cfgEl('cfg_trial_days') && cfgEl('cfg_trial_days').value) || 15),
        grace_days: Number((cfgEl('cfg_grace_days') && cfgEl('cfg_grace_days').value) || 5),
        read_only_days: Number((cfgEl('cfg_read_only_days') && cfgEl('cfg_read_only_days').value) || 10),
        msg_trial: (cfgEl('cfg_msg_trial') && cfgEl('cfg_msg_trial').value) || '',
        msg_grace: (cfgEl('cfg_msg_grace') && cfgEl('cfg_msg_grace').value) || '',
        msg_read_only: (cfgEl('cfg_msg_read_only') && cfgEl('cfg_msg_read_only').value) || '',
        msg_suspended: (cfgEl('cfg_msg_suspended') && cfgEl('cfg_msg_suspended').value) || ''
    };

    try {
        var response = await fetch(baseUrl + 'saveClienteEstadoConfigAjax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        var responseData = await response.json();
        if (!response.ok || responseData.error) {
            alert(responseData.message || 'No se pudo guardar la configuracion del cliente.');
            return;
        }

        alert('Configuracion de estados guardada.');
    } catch (error) {
        alert('No se pudo guardar la configuracion del cliente.');
    }
}

document.addEventListener('change', function (e) {
    if (!e.target || e.target.id !== 'estadoConfigClienteCodigo') return;
    loadClienteEstadoConfig(e.target.value || '');
});

document.addEventListener('click', async function (e) {
    var target = e.target;
    if (!target) return;

    if (target.id === 'saveConfigGeneral') {
        var bookingEmailInput = document.getElementById('bookingEmailConfig');
        var payload = {
            emailReservas: bookingEmailInput ? bookingEmailInput.value : ''
        };

        try {
            var response = await fetch(baseUrl + 'saveConfigGeneral', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            var responseData = await response.json();
            if (!response.ok || responseData.error) {
                alert(responseData.message || 'No se pudo completar la operacion.');
                return;
            }
            alert('Configuracion guardada correctamente.');
        } catch (error) {
            alert('No se pudo completar la operacion.');
        }
        return;
    }

    if (target.id === 'resetClienteEstadoConfig') {
        setClienteEstadoForm(CLIENTE_ESTADO_DEFAULTS);
        return;
    }

    if (target.id === 'saveClienteEstadoConfig') {
        var clienteSel = cfgEl('estadoConfigClienteCodigo');
        await saveClienteEstadoConfig(clienteSel ? clienteSel.value : '');
    }
});
