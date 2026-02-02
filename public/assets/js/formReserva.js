const confirmarReservabutton = document.getElementById('confirmarReserva')
const bookingForm = document.getElementById('bookingForm')
const selectCancha = document.getElementById('cancha')
const fechaInput = document.getElementById('fecha')
const horarioDesde = document.getElementById('horarioDesde')
const horarioHasta = document.getElementById('horarioHasta')
const inputMonto = document.getElementById('inputMonto')
const divMonto = document.getElementById('div-monto')
const nombre = document.getElementById('nombre')
const telefono = document.getElementById('telefono')
const codigoArea = document.getElementById('codigoArea')
const pagoReserva = document.getElementById('inputPagoReserva')
const pagoTotal = document.getElementById('switchPagoTotal')
const divTime = document.getElementById('div-time')
const divTimeH = document.getElementById('div-time-h')
const modalConfirmarReserva = new bootstrap.Modal('#modalConfirmarReserva')
const modalSpinner = new bootstrap.Modal('#modalSpinner')
const modalIngresarPago = new bootstrap.Modal('#ingresarPago')
const modalResult = new bootstrap.Modal('#modalResult')
const contentBookingResult = document.getElementById('bookingResult')
const divSelectCancha = document.getElementById('divSelectCancha')
const powerOff = document.getElementsByName('powerOff')
const welcomeModal = new bootstrap.Modal('#welcomeModal')
const ofertaModal = new bootstrap.Modal('#ofertaModal')

let data = {}
let preferencesIds = {}
let useOffer = false
// let idCustomer

let dataOferta = {
    valor: 0,
    descripcion: '',
    fecha: 0,
}

// Fecha actual por defecto
document.addEventListener('DOMContentLoaded', (e) => {
    if (esDomingo === '1') {
        checkSunday()
    }

    const fechaSistema = new Date()
    const año = fechaSistema.getFullYear()
    const mes = String(fechaSistema.getMonth() + 1).padStart(2, '0')
    const dia = String(fechaSistema.getDate()).padStart(2, '0')
    const fechaActual = `${año}-${mes}-${dia}`

    // const fechaActual = new Date().toISOString().split('T')[0]
    fechaInput.setAttribute('min', fechaActual)
    fechaInput.value = fechaActual;
    deleteRejected()

    welcomeModal.show()
})

document.addEventListener('change', async (e) => {
    if (e.target) {
        if (e.target.id == 'fecha') {
            const day = new Date(fechaInput.value);
            const dayOfWeek = day.getDay();

            if(esDomingo === '1' && dayOfWeek === 6){
                return alert('Ese día permanecerán cerradas las canchas')
            }

            selectCancha.selectedIndex = 0
            horarioDesde.selectedIndex = 0
            horarioHasta.selectedIndex = 0

        } else if (e.target.id == 'horarioDesde') {
            divTime.classList.remove('d-none')
            divTimeH.style.width = '49%'
            selectCancha.classList.remove('d-none')

            getTimeFromBookings()

            if (horarioDesde.value) {
                const indexDe = horarioDesde.selectedIndex
                horarioHasta.value = horarioHasta[indexDe + 1].value
            }

            inputMonto.value = 0

            getAmount()

        } else if (e.target.id == 'cancha') {

            if (!sessionUserLogued) {
                divMonto.classList.remove('d-none')
            }

            getAmount(selectCancha.value)

        } else if (e.target.id == 'horarioHasta') {
            inputMonto.value = 0

            getAmount(selectCancha.value)

        } else if (e.target.id == 'switchPagoTotal') {
            const btnMpParcial = document.getElementById('checkout-btn-parcial')
            const btnMpTotal = document.getElementById('checkout-btn-total')

            if (pagoTotal.checked) {
                btnMpParcial.style.display = 'none'
                btnMpTotal.style.display = 'block'
            } else {
                btnMpParcial.style.display = 'block'
                btnMpTotal.style.display = 'none'
            }
        }
    }
})


document.addEventListener('click', async (e) => {
    if (e.target) {
        const rate = await getRate()

        if (sessionUserLogued) {
            data = {
                fecha: fecha.value,
                cancha: selectCancha.value,
                horarioDesde: horarioDesde.value,
                horarioHasta: horarioHasta.value,
                nombre: nombre.value,
                telefono: telefono.value,
                codigoArea: codigoArea.value,
            }
        } else {
            data = {
                fecha: fecha.value,
                cancha: cancha.value,
                horarioDesde: horarioDesde.value,
                horarioHasta: horarioHasta.value,
                nombre: nombre.value,
                telefono: telefono.value,
                codigoArea: codigoArea.value,
                monto: pagoReserva.value,
                total: inputMonto.value,
                parcial: inputMonto.value * rate / 100,
                diferencia: inputMonto.value - pagoReserva.value,
                reservacion: pagoReserva.value,
                pagoTotal: pagoTotal.checked,
                metodoDePago: 'Mercado Pago',
                oferta: useOffer,
                // idCliente: idCustomer,
            }
        }

        if (e.target.id == 'confirmarReserva') {
            if (fecha.value == '' || cancha.value == '' || horarioDesde.value == '' || horarioHasta.value == '' || nombre.value == '' || telefono.value == '' || codigoArea.value == '') {
                alert('Debe completar todos los datos')
                return;
            } else {
                await setScriptMP(inputMonto.value)

            }

            if (horarioDesde.value == '23' && horarioHasta.value == '00' || horarioDesde.value == '23' && horarioHasta.value == '01' || horarioDesde.value == '22' && horarioHasta.value == '00' || horarioDesde.value == '22' && horarioHasta.value == '01') {
            } else if (parseInt(horarioDesde.value) >= parseInt(horarioHasta.value)) {
                alert('El horario de comienzo no puede ser mayor al de fin')
                return;
            }

            fetchFormInfo(data)

        } else if (e.target.id == 'buttonCancel' || e.target.id == 'btnClose' || e.target.id == 'cancelarReserva') {
            location.reload(true)
        } else if (e.target.id == 'switchPagoTotal') {
            const switchPagoTotal = document.getElementById('switchPagoTotal')
            const nocturnalTime = await getNocturnalTime()
            const rate = await getRate()

            if (switchPagoTotal.checked) {
                if (nocturnalTime.time.includes(horarioDesde.value) && nocturnalTime.time.includes(horarioHasta.value)) {
                    pagoReserva.value = inputMonto.value
                } else {
                    pagoReserva.value = inputMonto.value
                }
            } else {
                pagoReserva.value = inputMonto.value * rate / 100
            }
        } else if (e.target.id == 'abonarReservaBoton') { //Por defecto me va a traer el valor del porcentual
            alert('Sr cliente, al abonar una reserva (sea de manera parcial o total) asume el compromiso y la responsabilidad de la asistencia. Caso contrario no habrá devoluciones de dinero y los movimientos de reserva quedarán sujetos a disponibilidad. Así mismo, en caso de llegar tarde a la cancha, el tiempo de juego será hasta la fecha reservada.')

            const rate = await getRate()
            modalIngresarPago.show()
            pagoReserva.value = inputMonto.value * rate / 100

        } else if (e.target.id == 'confirmBooking') {
            const amount = document.getElementById('adminBookingAmount')
            const paymentMethod = document.getElementById('adminPaymentMethod')
            const description = document.getElementById('adminBookingDescription')
            const totalReserva = document.getElementById('adminBookingTotalAmount')

            data.monto = amount.value
            data.metodoDePago = paymentMethod.value
            data.descripcion = description.value
            data.total = totalReserva.value

            saveAdminBooking(data)
        } else if (e.target.id == 'confirmarAdminReserva') {
            fetchFormInfo(data)

            modalConfirmarReserva.show()
        }
    }
})

function checkSunday() {
    const today = new Date();
    const dayOfWeek = today.getDay();
    const form = document.getElementById("formBooking")
    const container = document.getElementById("isSunday")

    if (dayOfWeek === 0) {
        form.classList.add("d-none")
        container.classList.remove("d-none")
    }
}


telefono.addEventListener('input', async () => {

    let content

    const phone = String(codigoArea.value + telefono.value)

    if (phone.length == 10) {
        modalSpinner.show()
        const offer = await getOffer()
        const customer = await getCustomer(phone)
        const amount = inputMonto.value


        if (customer) {

            if (customer.offer == '1') {

                content = `
                    <h1 class="offerTitle">${offer.value}%</h1>
                    <h6 class="offerDescription">${offer.description}</h6>
                    <button type="button" class="btn mb-2" data-bs-dismiss="modal" style="background-color: #f09424">Continuar</button>
                    `

                if (offer.value != 0) {
                    const ofertaModalContent = document.getElementById('ofertaModalContent')

                    ofertaModalContent.innerHTML = content
                    ofertaModal.show()
                }

                const discount = amount * offer.value / 100
                const discountAmount = amount - discount

                useOffer = true
                // idCustomer = customer.id
                inputMonto.value = discountAmount
                nombre.value = customer.name

            } else {
                // idCustomer = customer.id
                nombre.value = customer.name
            }
        } else {

            const nocturnalTime = await getNocturnalTime()
            const selectedField = await getField(selectCancha.value)


            if (nocturnalTime.time.includes(horarioDesde.value) && nocturnalTime.time.includes(horarioHasta.value)) {
                inputMonto.value = `${calculateAmount(horarioDesde.value, horarioHasta.value, selectedField.ilumination_value)}`
            } else {
                inputMonto.value = `${calculateAmount(horarioDesde.value, horarioHasta.value, selectedField.value)}`
            }
        }

        setTimeout(() => { modalSpinner.hide() }, 300);
    }
})

async function deleteRejected() {
    try {
        const response = await fetch(`${baseUrl}deleteRejected`);

        const responseData = await response.json();

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}


async function saveAdminBooking(data) {
    modalIngresarPago.hide()

    try {
        const response = await fetch(`${baseUrl}saveAdminBooking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {

            modalResult.show()

            contentBookingResult.innerHTML = `
            <div class="modal-body modalResultPayment d-flex justify-content-center align-items-center flex-column" style="background-color: #157347; color: #fff">
                <h4 class="mb-5">Reserva confirmada!</h4>
                <i class="fa-regular fa-circle-check fa-2xl" style="margin-bottom: 20px;"></i>
            </div>`
        }

        location.reload(true)
        // const responseData = await response.json();

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function setScriptMP(amount) {
    let preference = {}

    modalSpinner.show()

    preference = {
        amount: amount,
    }

    const preferences = await setPreference(`${baseUrl}setPreference`, preference)
    const mp = new MercadoPago(publicKeyMp, {
        locale: "es-AR"
    })

    mp.checkout({
        preference: {
            id: preferences.preferenceIdParcial
        },
        render: {
            container: '#checkout-btn-parcial',
            label: 'Pagar con Mercado Pago'
        }
    })

    mp.checkout({
        preference: {
            id: preferences.preferenceIdTotal
        },
        render: {
            container: '#checkout-btn-total',
            label: 'Pagar con Mercado Pago'
        }
    })


    data.preferenceIdParcial = preferences.preferenceIdParcial,
        data.preferenceIdTotal = preferences.preferenceIdTotal,


        saveBooking(data)

    modalSpinner.hide()
    modalConfirmarReserva.show()
}


async function savePreferenceIds(data) {
    try {
        const response = await fetch(`${baseUrl}savePreferenceIds`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function setPreference(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        return responseData.data

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}


async function getAmount(field = "1") {
    try {
        const nocturnalTime = await getNocturnalTime()
        const selectedField = await getField(field)

        if (nocturnalTime.time.includes(horarioDesde.value) && nocturnalTime.time.includes(horarioHasta.value)) {
            inputMonto.value = `${calculateAmount(horarioDesde.value, horarioHasta.value, selectedField.ilumination_value)}`
        } else {
            inputMonto.value = `${calculateAmount(horarioDesde.value, horarioHasta.value, selectedField.value)}`
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function getRate() {
    try {
        const response = await fetch(`${baseUrl}getRate`);
        const responseData = await response.json();


        if (responseData.data != '') {

            return responseData.data.value
        } else {
            alert('Algo salió mal. No se pudo obtener la información.');
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function getOffer() {
    try {
        const response = await fetch(`${baseUrl}getOffersRate`);
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


async function getNocturnalTime() {
    try {
        const response = await fetch(`${baseUrl}getNocturnalTime`);
        const responseData = await response.json();

        if (responseData.data != '') {

            const nocturnalTime = { time: responseData.data }

            return nocturnalTime
        } else {
            alert('Algo salió mal. No se pudo obtener la información.');
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}


async function getFields() {
    try {
        const response = await fetch(`${baseUrl}getFields`);

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

// Busca la cancha seleccionada para colocar valor
async function getField(id) {
    try {
        const response = await fetch(`${baseUrl}getField/${id}`);

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


// Guarda reserva
async function saveBooking(data) {

    try {
        const response = await fetch(`${baseUrl}saveBooking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// Trae la información a mostrar en el modal
async function fetchFormInfo(data) {
    try {
        const response = await fetch(`${baseUrl}formInfo`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        if (responseData.data != '') {
            fillModal(responseData);
        } else {
            alert('Algo salió mal. No se pudo obtener la información.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function getCustomer(phone) {
    try {
        const response = await fetch(`${baseUrl}getCustomer/${phone}`);

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

// Rellena el modal
async function fillModal(data) {

    const modalBody = document.querySelector('.modal-resume-body')
    let amount = inputMonto.value

    if (data == '') {
        return;
    }

    let info = '';

    const fecha = convertDateFormat(data.data.fecha)

    if (sessionUserLogued) {
        info =
            `
        <ul id="bookingDetail">
            <li><i class="fa-solid fa-calendar-days"></i> <b>Fecha:</b> ${fecha}</li>
            <li><i class="fa-solid fa-futbol"></i> <b>Cancha:</b> ${data.data.cancha}</li>
            <li><i class="fa-solid fa-clock"></i> <b>Horario:</b> ${data.data.horarioDesde}:00 a ${data.data.horarioHasta}:00</li>
            <li><i class="fa-solid fa-user"></i> <b>Nombre:</b> ${data.data.nombre}</li>
            <li><i class="fa-solid fa-phone"></i> <b>Teléfono:</b> ${data.data.codigoArea + data.data.telefono}</li>
        </ul>
        `;
    } else {
        info =
            `
        <ul id="bookingDetail">
            <li><i class="fa-solid fa-calendar-days"></i> <b>Fecha:</b> ${fecha}</li>
            <li><i class="fa-solid fa-futbol"></i> <b>Cancha:</b> ${data.data.cancha}</li>
            <li><i class="fa-solid fa-clock"></i> <b>Horario:</b> ${data.data.horarioDesde}:00 a ${data.data.horarioHasta}:00</li>
            <i class="fa-regular fa-money-bill-1"></i> <b>Monto:</b> $${amount}</li>
            <li><i class="fa-solid fa-user"></i> <b>Nombre:</b> ${data.data.nombre}</li>
            <li><i class="fa-solid fa-phone"></i> <b>Teléfono:</b> ${data.data.codigoArea + data.data.telefono}</li>
        </ul>
        `;
    }



    modalBody.innerHTML = info;
}

function convertDateFormat(date) {
    return date.split("-").reverse().join("/")
}


// Trae los horarios de las reservas hechas
async function getTimeFromBookings() {
    const fecha = document.getElementById('fecha').value


    try {
        const response = await fetch(`${baseUrl}getBookings/${fecha}`);
        const responseData = await response.json();

        if (responseData.data != '') {


            getFieldForTimeBookings(responseData.data)
        } else {
            alert('Algo salió mal. No se pudo obtener la información.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// // Quita las canchas sin horario disponible seleccionado
// async function getFieldForTimeBookings(timeBookings) {
//     const currentReserva = [horarioDesde.value, horarioHasta.value]
//     const options = selectCancha.options; //canchas (4)

//     timeBookings.forEach(element => {
//         let reserva = []
//         for (let t = 0; t < element.time.length; t += 2) {
//             reserva.push(element.time.slice(t, t + 2))
//         }


//         const remove = element.time.includes(horarioDesde.value) && element.time.includes(horarioHasta.value)

//         if (remove) {
//             for (let i = 0; i < options.length; i++) {

//                 if (options[i].value == element.id_cancha) {
//                     options[i].remove()

//                     break
//                 }
//             }
//         } else {
//             let exists = false

//             for (let i = 0; i < options.length; i++) {
//                 if (options[i].value == element.id_cancha) {
//                     exists = true

//                     break
//                 }
//             }

//             if (!exists) {
//                 const newOption = new Option(element.nombre_cancha, element.id_cancha)
//                 selectCancha.appendChild(newOption)
//             }
//         }
//     })

//     const optionsArray = Array.from(selectCancha.options)

//     optionsArray.sort((a, b) => {
//         const valueA = parseFloat(a.value)
//         const valueB = parseFloat(b.value)
//         return valueA - valueB
//     });

//     selectCancha.innerHTML = ''

//     if (optionsArray.length == 1) {
//         selectCancha.setAttribute('disabled', 'true')
//         selectCancha.style.backgroundColor = '#bb2d3b'
//         optionsArray[0].innerText = 'No hay canchas disponibles en este horario'
//     } else {
//         selectCancha.removeAttribute('disabled')
//         selectCancha.style.backgroundColor = ''
//         optionsArray[0].innerText = 'Canchas disponibles'
//     }

//     optionsArray.forEach(option => {
//         selectCancha.appendChild(option)
//     })
// }


async function getFieldForTimeBookings(timeBookings) {
    let exists = false
    const currentReserva = [horarioDesde.value, horarioHasta.value] //21 a 22
    const options = selectCancha.options //canchas (4)


    timeBookings.forEach(element => {
        let reserva = []
        for (let t = 0; t < element.time.length; t += 2) {
            if (horarioDesde.value == element.time[t]) {
                reserva.push(element.time.slice(t, t + 2))
            }
        }

        exists = false

        if (reserva.length == 0) {
            for (let i = 0; i < options.length; i++) {
                if (options[i].value == element.id_cancha) {
                    exists = true

                    break
                }
            }

            if (!exists) {
                const newOption = new Option(element.nombre_cancha, element.id_cancha)
                selectCancha.appendChild(newOption)
            }
        } else {

            reserva.forEach(res => {
                const remove = JSON.stringify(res) === JSON.stringify(currentReserva)

                if (remove) {
                    for (let i = 0; i < options.length; i++) {

                        if (options[i].value == element.id_cancha) {
                            options[i].remove()

                            break
                        }
                    }
                } else {
                    exists = false

                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value == element.id_cancha) {
                            exists = true

                            break
                        }
                    }

                    if (!exists) {
                        const newOption = new Option(element.nombre_cancha, element.id_cancha)
                        selectCancha.appendChild(newOption)
                    }
                }
            })
        }
    })

    const optionsArray = Array.from(selectCancha.options)

    optionsArray.sort((a, b) => {
        const valueA = parseFloat(a.value)
        const valueB = parseFloat(b.value)
        return valueA - valueB
    });

    selectCancha.innerHTML = ''

    if (optionsArray.length == 1) {
        selectCancha.setAttribute('disabled', 'true')
        selectCancha.style.backgroundColor = '#bb2d3b'
        optionsArray[0].innerText = 'No hay canchas disponibles en este horario'
    } else {
        selectCancha.removeAttribute('disabled')
        selectCancha.style.backgroundColor = ''
        optionsArray[0].innerText = 'Canchas disponibles'
    }

    optionsArray.forEach(option => {
        selectCancha.appendChild(option)
    })
}




// Calcula el total $ de la reserva

function calculateAmount(from, until, amount) {
    let hours = 0
    let result = ''

    if (Number(from) == 23 && Number(until) == 0) {
        hours = 1
    } else if (Number(from) == 23 && Number(until == 1)) {
        hours = 2
    }

    for (i = Number(from); i < Number(until); i++) {

        hours = hours + 1
    }

    result = parseInt(hours) * parseInt(amount)

    return result
}