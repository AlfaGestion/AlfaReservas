document.addEventListener('click', async function (e) {
    var target = e.target ? e.target.closest('button') || e.target : null;
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

    if (target.id === 'openNewUserModalBtn') {
        var newUserForm = document.getElementById('newUserForm');
        if (newUserForm) {
            newUserForm.reset();
        }
        return;
    }

    if (target.id === 'saveNewUserBtn') {
        var saveBtn = target;
        var passwordValue = (document.getElementById('newUserPassword') || {}).value || '';
        var passwordRule = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/;
        if (!passwordRule.test(passwordValue)) {
            alert('La contrasena debe tener al menos una mayuscula, una minuscula y un numero.');
            return;
        }
        var payload = new URLSearchParams();
        payload.append('user', (document.getElementById('newUserUser') || {}).value || '');
        payload.append('email', (document.getElementById('newUserEmail') || {}).value || '');
        payload.append('password', passwordValue);
        payload.append('repeat_password', (document.getElementById('newUserRepeatPassword') || {}).value || '');

        saveBtn.disabled = true;

        try {
            var response = await fetch(baseUrl + 'saveUserAjax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: payload.toString()
            });
            var responseData = await response.json();

            if (!response.ok || responseData.error) {
                alert(responseData.message || 'No se pudo guardar el usuario.');
                return;
            }

            if (responseData.data && Array.isArray(responseData.data.users)) {
                renderUsersTable(responseData.data.users);
            }

            var form = document.getElementById('newUserForm');
            if (form) {
                form.reset();
            }

            var modalEl = document.getElementById('newUserModal');
            if (modalEl && window.bootstrap && bootstrap.Modal) {
                var modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modalInstance.hide();
            }

            alert(responseData.message || 'Usuario creado correctamente.');
        } catch (error) {
            alert('No se pudo guardar el usuario.');
        } finally {
            saveBtn.disabled = false;
        }
    }
});

document.addEventListener('submit', function (e) {
    if (e.target && e.target.id === 'newUserForm') {
        e.preventDefault();
        var saveBtn = document.getElementById('saveNewUserBtn');
        if (saveBtn) {
            saveBtn.click();
        }
    }
});

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderUsersTable(users) {
    var tbody = document.getElementById('usersTableBody');
    if (!tbody) return;

    if (!users || !users.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No hay usuarios para mostrar.</td></tr>';
        return;
    }

    var rows = users.map(function (u) {
        var profile = Number(u.superadmin || 0) === 1 ? 'Superadmin' : 'Admin';
        var status = Number(u.active || 0) === 1 ? 'Activo' : 'Inactivo';
        return '<tr>' +
            '<td>' + escapeHtml(u.user || '-') + '</td>' +
            '<td>' + escapeHtml(u.name || '-') + '</td>' +
            '<td>' + escapeHtml(u.email || '-') + '</td>' +
            '<td>' + profile + '</td>' +
            '<td>' + status + '</td>' +
            '</tr>';
    });

    tbody.innerHTML = rows.join('');
}
