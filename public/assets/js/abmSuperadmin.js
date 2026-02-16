const formBooking = document.getElementById('formBooking')
const selectMenuAbm = document.getElementById('selectMenuAbm')
const openingTime = document.getElementById('openingTime')
const switchCutTime = document.getElementById('switchCutTime')
const horarioNocturno = document.getElementById('horarioNocturno')
const inputCompletarPagoReserva = document.getElementById('inputCompletarPagoReserva')
const inputRate = document.getElementById('rate')
const inputOfferRate = document.getElementById('offerRate')
const descriptionOffer = document.getElementById('descriptionOffer')
const medioPagoSelect = document.getElementById('medioPagoSelect')
const changeTimeFrom = document.getElementById('changeTimeFrom')
const changeTimeUntil = document.getElementById('changeTimeUntil')
const changeTimeFromCut = document.getElementById('changeTimeFromCut')
const changeTimeUntilCut = document.getElementById('changeTimeUntilCut')
const completarPagoModalB = new bootstrap.Modal('#completarPagoModal')
const cambiarEstadoMPModal = new bootstrap.Modal('#modalCambiarEstado')
const cancelBookingModal = new bootstrap.Modal('#eliminarReservaModal')
const editBookingModal = new bootstrap.Modal('#editarReservaModal')
const completarPagoModal = document.getElementById('completarPagoModal')
const spinnerCompletarPago = new bootstrap.Modal('#spinnerCompletarPago')
const modalResultPayment = new bootstrap.Modal('#modalResultPayment')
const contentPaymentResult = document.getElementById('paymentResult')
const enterFieldsForm = document.getElementById('enterFields')
const selectEditField = document.getElementById('selectEditField')
const editFieldDiv = document.getElementById('editFieldDiv')
const selectEditFields = document.getElementById('selectEditFields')
const adminTabs = new bootstrap.Tab(document.getElementById('nav-tab'))
const toggleCancelReservations = document.getElementById('toggleCancelReservations')
const cancelReservationsPanel = document.getElementById('cancelReservationsPanel')
const cancelDateInput = document.getElementById('cancelDate')
const cancelFieldSelect = document.getElementById('cancelField')
const cancelReservationsResult = document.getElementById('cancelReservationsResult')
const existingClosures = document.getElementById('existingClosures')
const configPanel = document.getElementById('configPanel')
const closureTextConfig = document.getElementById('closureTextConfig')
const bookingEmailConfig = document.getElementById('bookingEmailConfig')
let idBooking

if (adminTabs && adminTabs._element) {
    adminTabs._element.addEventListener("shown.bs.tab", (e) => {
        if (enterFieldsForm) enterFieldsForm.classList.add('d-none')
        if (selectEditField) selectEditField.classList.add('d-none')
    })
}

if (cancelDateInput) {
    const today = new Date().toISOString().split('T')[0]
    cancelDateInput.value = today
    if (cancelFieldSelect) {
        refreshExistingClosures()
    }
}

if (selectEditField) {
    selectEditField.addEventListener('change', async (e) => {
        if (selectEditFields) {
            getEditField(selectEditFields.value)
        }
    })
}


document.addEventListener('click', async (e) => {
    if (e.target) {
        if (e.target.id == 'botonCompletarPago') {

            const idUser = document.getElementById('userId').dataset.id
            const botonPagar = document.getElementById('botonCompletarPago')
            const bookingId = botonPagar.dataset.id
            const booking = await getBooking(bookingId)

            if (medioPagoSelect.value == '' || inputCompletarPagoReserva.value == '') {
                return alert('Debe completar todos los campos obligatorios.')
            }

            if (Number(inputCompletarPagoReserva.value) > Number(booking.diference)) {
                return alert('El monto a abonar no puede ser mayor al saldo.')
            }

            let data = {
                pago: inputCompletarPagoReserva.value,
                idUser: idUser,
                medioPago: medioPagoSelect.value,
                idCustomer: booking.id_customer,
            }

            completePayment(`${baseUrl}completePayment/${bookingId}`, data)

        } else if (e.target.id == 'saveRate') {

            let data = {
                value: inputRate.value,
            }

            saveRate(`${baseUrl}saveRate`, data)

        } else if (e.target.id == 'saveOfferRate') {

            let data = {
                value: inputOfferRate.value,
                description: descriptionOffer.value
            }

            saveOfferRate(`${baseUrl}saveOfferRate`, data)

        } else if (e.target.id == 'modalCompletarPago') {

            const bookingId = e.target.dataset.id
            const botonPagar = document.getElementById('botonCompletarPago')
            const booking = await getBooking(bookingId)
            botonPagar.setAttribute('data-id', bookingId)

            completarPagoModalB.show()
            inputCompletarPagoReserva.value = booking.diference

        } else if (e.target.id == 'buttonCreateField') {

            const editFieldsForm = document.getElementById('editFields')

            enterFieldsForm.classList.remove('d-none')
            editFieldsForm.classList.add('d-none')
            selectEditField.classList.add('d-none')

        } else if (e.target.id == 'buttonEditField') {

            selectEditField.classList.remove('d-none')
            enterFieldsForm.classList.add('d-none')

        } else if (e.target.id == 'eliminarReservaModal') {
            idBooking = e.target.dataset.id

            cancelBookingModal.show()
        } else if (e.target.id == 'cancelCancelBooking') {
            cancelBookingModal.hide()

        } else if (e.target.id == 'confirmCancelBooking') {
            let dataCancel = {
                idBooking: idBooking
            }

            cancelBooking(dataCancel)
        } else if (e.target.id == 'editarReservaModal') {

            editBookingModal.show()
        } else if (e.target.id == 'toggleCancelReservations') {
            if (cancelReservationsPanel) {
                cancelReservationsPanel.classList.toggle('d-none')
                if (!cancelReservationsPanel.classList.contains('d-none')) {
                    refreshExistingClosures()
                }
            }
        } else if (e.target.id == 'toggleConfigPanel') {
            if (configPanel) {
                configPanel.classList.toggle('d-none')
            }
        } else if (e.target.id == 'closeCancelReservations') {
            if (cancelReservationsPanel) {
                cancelReservationsPanel.classList.add('d-none')
            }
        } else if (e.target.id == 'closeConfigPanel') {
            if (configPanel) {
                configPanel.classList.add('d-none')
            }
        } else if (e.target.id == 'confirmCancelReservations') {
            if (!cancelDateInput || !cancelFieldSelect) return
            const payload = {
                fecha: cancelDateInput.value,
                cancha: cancelFieldSelect.value,
            }
            const force = e.target.dataset.force === '1'
            if (!force) {
                const result = await checkCancelReservations(payload)
                if (!result) return
                const bookings = result.bookings || []
                if (bookings.length === 0) {
                    await saveCancelReservations(payload)
                } else {
                    renderCancelReservationsResult(result)
                }
                return
            }
            await saveCancelReservations(payload)
            await refreshExistingClosures()
        } else if (e.target.id == 'saveConfigGeneral') {
            const payload = {
                textoCierre: closureTextConfig ? closureTextConfig.value : '',
                emailReservas: bookingEmailConfig ? bookingEmailConfig.value : '',
            }
            await saveConfigGeneral(payload)
            if (configPanel) {
                configPanel.classList.add('d-none')
            }
        } else if (e.target.id == 'cancelCancelReservations') {
            if (cancelReservationsResult) {
                cancelReservationsResult.innerHTML = ''
            }
        }
        const deleteBtn = e.target.closest('.delete-closure')
        if (deleteBtn) {
            const id = deleteBtn.dataset.id
            if (!id) return
            await deleteCancelReservation({ id })
            await refreshExistingClosures()
        }
    }
})

if (cancelDateInput) {
    cancelDateInput.addEventListener('change', refreshExistingClosures)
}
if (cancelFieldSelect) {
    cancelFieldSelect.addEventListener('change', refreshExistingClosures)
}

async function editBooking(data) {
    try {
        const response = await fetch(`${baseUrl}editBooking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Reserva actualizada correctamente.')

        } else {
            alert('No se pudo completar la operacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}


async function cancelBooking(data) {
    try {
        const response = await fetch(`${baseUrl}cancelBooking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Reserva anulada correctamente.')

            cancelBookingModal.hide()
            location.reload(true)

        } else {
            alert('No se pudo completar la operacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function checkCancelReservations(data) {
    try {
        const response = await fetch(`${baseUrl}checkCancelReservations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();
        if (responseData.error) {
            alert(responseData.message || 'No se pudo completar la operacion. Intenta nuevamente.')
            return null
        }
        return responseData.data
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function saveCancelReservations(data) {
    try {
        const response = await fetch(`${baseUrl}saveCancelReservations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();
        if (!response.ok || responseData.error) {
            alert(responseData.message || 'No se pudo completar la operacion. Intenta nuevamente.')
            return
        }

        alert('Cierre de cancha informado correctamente.')
        if (cancelReservationsResult) {
            cancelReservationsResult.innerHTML = ''
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function getCancelReservations(data) {
    try {
        const response = await fetch(`${baseUrl}getCancelReservations`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();
        if (responseData.error) {
            return []
        }
        return responseData.data || []
    } catch (error) {
        console.error('Error:', error);
        return []
    }
}

async function deleteCancelReservation(data) {
    try {
        const response = await fetch(`${baseUrl}deleteCancelReservation`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();
        if (!response.ok || responseData.error) {
            alert(responseData.message || 'No se pudo completar la operacion. Intenta nuevamente.')
            return
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

async function refreshExistingClosures() {
    if (!cancelDateInput || !existingClosures) return
    const payload = {
        fecha: cancelDateInput.value,
        cancha: cancelFieldSelect ? cancelFieldSelect.value : 'all',
    }
    const rows = await getCancelReservations(payload)
    if (!rows || rows.length === 0) {
        existingClosures.innerHTML = ''
        return
    }
    const formatDate = (dateStr) => {
        if (!dateStr || typeof dateStr !== 'string' || !dateStr.includes('-')) return dateStr || ''
        const [y, m, d] = dateStr.split('-')
        if (!y || !m || !d) return dateStr
        return `${d}/${m}/${y}`
    }
    existingClosures.innerHTML = `
        <div class="alert alert-info mb-2">Cierres informados para esta fecha/cancha</div>
        <ul class="list-group">
            ${rows.map(r => `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span>${formatDate(r.cancel_date)} - ${r.field_label} (por ${r.user_name})</span>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-closure" data-id="${r.id}">Cancelar</button>
                </li>
            `).join('')}
        </ul>
    `
}

async function saveConfigGeneral(data) {
    try {
        const response = await fetch(`${baseUrl}saveConfigGeneral`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();
        if (!response.ok || responseData.error) {
            alert(responseData.message || 'No se pudo completar la operacion. Intenta nuevamente.')
            return
        }

        alert('Configuracion guardada correctamente.')
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

function renderCancelReservationsResult(result, payload) {
    if (!cancelReservationsResult) return
    if (!result) {
        cancelReservationsResult.innerHTML = ''
        return
    }

    const bookings = result.bookings || []
    let html = ''
    const formatDate = (dateStr) => {
        if (!dateStr || typeof dateStr !== 'string' || !dateStr.includes('-')) return dateStr || ''
        const [y, m, d] = dateStr.split('-')
        if (!y || !m || !d) return dateStr
        return `${d}/${m}/${y}`
    }
    const fechaLabel = formatDate(result.fecha)

    if (bookings.length > 0) {
        html += `
            <div class="alert alert-warning">
                Se encontraron reservas para ${fechaLabel} (${result.canchaLabel}). Revisa antes de informar el cierre.
            </div>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Telefono</th>
                            <th>Cancha</th>
                            <th>Horario</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${bookings.map(b => `
                            <tr>
                                <td>${b.nombre}</td>
                                <td>${b.telefono}</td>
                                <td>${b.cancha}</td>
                                <td>${b.horario}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `
    } else {
        html += `
            <div class="alert alert-success">
                No hay reservas para ${fechaLabel} (${result.canchaLabel}).
            </div>
        `
    }

    html += `
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" id="confirmCancelReservations" data-force="1">Aceptar igual</button>
            <button type="button" class="btn btn-secondary" id="cancelCancelReservations">Cancelar</button>
        </div>
    `

    cancelReservationsResult.innerHTML = html
}

async function completePayment(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            if (response.ok) {

                setTimeout(() => { spinnerCompletarPago.show() }, 500)

                completarPagoModalB.hide()

                contentPaymentResult.innerHTML = `
                <div class="modal-body modalResultPayment d-flex justify-content-center align-items-center flex-column" style="background-color: #157347;">
                    <h4 class="mb-5">Pago confirmado!</h4>
                    <i class="fa-regular fa-circle-check fa-2xl" style="margin-bottom: 20px;"></i>
                </div>`

                setTimeout(() => { modalResultPayment.show() }, 1000)
                setTimeout(() => { spinnerCompletarPago.hide() }, 1200)
                setTimeout(() => { modalResultPayment.hide() }, 2500)

                if (typeof getActiveBookings === 'function' && typeof bookingData !== 'undefined') {
                    getActiveBookings(bookingData)
                }

            } else {
                setTimeout(() => { spinnerCompletarPago.show() }, 500)
                completarPagoModalB.hide()

                contentPaymentResult.innerHTML = `
                <div class="modal-body modalResultPayment d-flex justify-content-center align-items-center flex-column" style="background-color: #bb2d3b;">
                    <h4 class="mb-5">No se pudo guardar el pago. Vuelva a intentar</h4>
                    <i class="fa-regular fa-circle-xmark fa-2xl" style="margin-bottom: 20px;"></i>
                </div>`

                setTimeout(() => { modalResultPayment.show() }, 2000)
                setTimeout(() => { spinnerCompletarPago.hide() }, 2000)
            }

        } else {
            alert('No se pudo completar la operacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function saveRate(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Porcentaje actualizado correctamente.')
            location.reload(true)

        } else {
            alert('No se pudo completar la operacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function saveOfferRate(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {
            alert('Oferta actualizada correctamente.')
            location.reload(true)

        } else {
            alert('No se pudo completar la operacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}


async function getBooking(id) {
    try {
        const response = await fetch(`${baseUrl}getBooking/${id}`);

        const responseData = await response.json();

        if (responseData.data != '') {

            return responseData.data

        } else {
            alert('No se pudo obtener la informacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function getEditField(id) {
    try {
        const response = await fetch(`${baseUrl}getField/${id}`);

        const responseData = await response.json();

        if (responseData.data != '') {

            fillDiv(responseData.data)

        } else {
            alert('No se pudo obtener la informacion. Intenta nuevamente.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

function fillDiv(field) {
    let div = ''

    let disabledCheck
    if (field.disabled == 1) { disabledCheck = 'checked' }

    div = `
        <div class="editFields" id="editFields">
            <form action="${webBaseUrl}editField/${field.id}" method="POST">

                <div class="form-check form-switch mt-4">
                    <input class="form-check-input" type="checkbox" role="switch" name="disabled" id="disableField" ${disabledCheck}>
                    <label class="form-check-label" for="disableField">Deshabilitar cancha</label>
                </div>

                <div class="input-group mt-3 mb-3">
                    <span class="input-group-text" id="basic-addon1">Nombre cancha</span>
                    <input type="text" class="form-control" value="${field.name}" name="nombre" placeholder="Ingrese el nombre de la cancha" aria-label="Nombre cancha" aria-describedby="basic-addon1">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon3">Medidas</span>
                    <input type="text" class="form-control" value="${field.sizes}" name="medidas" placeholder="Ingrese las medidas de la cancha" aria-label="Medidas" aria-describedby="basic-addon3">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon2">Tipo de piso</span>
                    <input type="text" class="form-control" value="${field.floor_type}" name="tipoPiso" placeholder="Ingrese el tipo de piso de la cancha" aria-label="Tipo piso" aria-describedby="basic-addon2">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon4">Tipo de cancha</span>
                    <input type="text" class="form-control" value="${field.field_type}" name="tipoCancha" placeholder="Ingrese el tipo de cancha (futbol 5, 7, 11)" aria-label="Tipo cancha" aria-describedby="basic-addon4">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="tipoTecho" role="switch" id="tipoTecho">
                    <label class="form-check-label" for="tipoTecho">Es techada</label>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">Valor sin iluminacion</span>
                    <input type="text" class="form-control" value="${field.value}" name="valor" placeholder="Ingrese valor por hora sin iluminacion" aria-label="Valor">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">Valor con iluminacion</span>
                    <input type="text" class="form-control" value="${field.ilumination_value}" name="valorIluminacion" placeholder="Ingrese valor por hora con iluminacion" aria-label="Valor">
                </div>

                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="${webBaseUrl}" type="button" class="btn btn-danger">Cancelar</a>
            </form>
        </div>
        `

    editFieldDiv.innerHTML = div
}

