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
let idBooking

adminTabs._element.addEventListener("shown.bs.tab", (e) => {
    enterFieldsForm.classList.add('d-none')
    selectEditField.classList.add('d-none')
})

selectEditField.addEventListener('change', async (e) => {
    getEditField(selectEditFields.value)
})


document.addEventListener('click', async (e) => {
    if (e.target) {
        if (e.target.id == 'botonCompletarPago') {

            const idUser = document.getElementById('userId').dataset.id
            const botonPagar = document.getElementById('botonCompletarPago')
            const bookingId = botonPagar.dataset.id
            const booking = await getBooking(bookingId)

            if (medioPagoSelect.value == '' || inputCompletarPagoReserva.value == '') {
                return alert('Debe completar todos los campos')
            }

            if (Number(inputCompletarPagoReserva.value) > Number(booking.diference)) {
                return alert('El monto a abonar no puede ser mayor al saldo')
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
        }
    }
})

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
            alert('Reserva eliminada con éxito')

        } else {
            alert('Algo salió mal. No se pudo eliminar la reserva.');
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
            alert('Reserva anulada con éxito')

            cancelBookingModal.hide()
            location.reload(true)

        } else {
            alert('Algo salió mal. No se pudo eliminar la reserva.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
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

                setTimeout(() => { modalResultPayment.show() }, 2000)
                setTimeout(() => { spinnerCompletarPago.hide() }, 2000)
                setTimeout(() => { location.reload(true) }, 3000)

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
                setTimeout(() => { location.reload(true) }, 3000)
            }

        } else {
            alert('Algo salió mal. No se pudo ingresar el pago.');
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
            alert('Valor ingresado con éxito')
            location.reload(true)

        } else {
            alert('Algo salió mal. No se pudo ingresar el valor.');
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
            alert('Valor ingresado con éxito')
            location.reload(true)

        } else {
            alert('Algo salió mal. No se pudo ingresar el valor.');
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
            alert('Algo salió mal. No se pudo obtener la información.');
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
            alert('Algo salió mal. No se pudo obtener la información.');
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
            <form action="${baseUrl}editField/${field.id}" method="POST">

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
                    <input type="text" class="form-control" value="${field.field_type}" name="tipoCancha" placeholder="Ingrese el tipo de cancha (fútbol 5, 7, 11)" aria-label="Tipo cancha" aria-describedby="basic-addon4">
                </div>

                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="tipoTecho" role="switch" id="tipoTecho">
                    <label class="form-check-label" for="tipoTecho">Es techada</label>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">Valor sin iluminación</span>
                    <input type="text" class="form-control" value="${field.value}" name="valor" placeholder="Ingrese valor por hora sin iluminación" aria-label="Valor">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text">Valor con iluminación</span>
                    <input type="text" class="form-control" value="${field.ilumination_value}" name="valorIluminacion" placeholder="Ingrese valor por hora con iluminación" aria-label="Valor">
                </div>

                <button type="submit" class="btn btn-success">Guardar</button>
                <a href="${baseUrl}" type="button" class="btn btn-danger">Cancelar</a>
            </form>
        </div>
        `

    editFieldDiv.innerHTML = div
}

