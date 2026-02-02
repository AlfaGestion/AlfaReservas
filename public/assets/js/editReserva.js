const fecha = document.getElementById('fecha')
const horarioDesde = document.getElementById('horarioDesde')
const horarioHasta = document.getElementById('horarioHasta')
const cancha = document.getElementById('cancha')
const inputMonto = document.getElementById('inputMonto')
const telefono = document.getElementById('telefono')
const nombre = document.getElementById('nombre')
const modalSpinner = new bootstrap.Modal('#modalSpinner')
const modalResult = new bootstrap.Modal('#modalResult')
const contentEditBookingResult = document.getElementById('bookingEditResult')


let bookingId
let updateData


document.addEventListener('DOMContentLoaded', (e) => {
    const fechaActual = new Date().toISOString().split('T')[0]
    fecha.setAttribute('min', fechaActual)
    fecha.value = fechaActual;
})

document.addEventListener('click', async (e) => {
    if (e.target) {
        const rate = await getRate()

        updateData = {
            fecha: fecha.value,
            cancha: cancha.value,
            horarioDesde: horarioDesde.value,
            horarioHasta: horarioHasta.value,
            total: inputMonto.value,
            parcial: inputMonto.value * rate / 100,
        }

        if (e.target.id == 'editarReservaModal') {
            bookingId = e.target.dataset.id

            const currentBooking = await getBooking(bookingId)

            fecha.value = currentBooking.date
            horarioDesde.value = currentBooking.time_from
            horarioHasta.value = currentBooking.time_until
            cancha.value = currentBooking.id_field
            telefono.value = currentBooking.phone
            nombre.value = currentBooking.name
            inputMonto.value = currentBooking.total

        } else if (e.target.id == 'actualizarReserva') {
            const currentBooking = await getBooking(bookingId)

            updateData.bookingId = bookingId
            updateData.diferencia = inputMonto.value - currentBooking.payment
            updateData.pagoTotal = (inputMonto.value - currentBooking.payment) == 0 ? 1 : 0


            if (fecha.value == '' || cancha.value == '' || horarioDesde.value == '' || horarioHasta.value == '' || nombre.value == '' || telefono.value == '') {
                alert('Debe completar todos los datos')
                return;
            }

            if (horarioDesde.value == '23' && horarioHasta.value == '00' || horarioDesde.value == '23' && horarioHasta.value == '01' || horarioDesde.value == '22' && horarioHasta.value == '00' || horarioDesde.value == '22' && horarioHasta.value == '01') {
            } else if (parseInt(horarioDesde.value) >= parseInt(horarioHasta.value)) {
                alert('El horario de comienzo no puede ser mayor al de fin')
                return;
            }

            updateBooking(updateData)
        } else if (e.target.id == 'cancelarReserva') {
            editBookingModal.hide()
        }
    }
})

document.addEventListener('change', async (e) => {
    if (e.target) {
        if (e.target.id == 'horarioDesde') {

            const indexDe = horarioDesde.selectedIndex
            horarioHasta.value = horarioHasta[indexDe + 1].value

            inputMonto.value = 0

            await getAmount(cancha.value)

            await getTimeFromBookings()


        } else if (e.target.id == 'fecha') {
            horarioDesde.selectedIndex = 0
            horarioHasta.selectedIndex = 0
            cancha.selectedIndex = 0
            inputMonto.value = 0

        } else if (e.target.id == 'cancha') {

            await getAmount(cancha.value)

        }
    }
})

async function updateBooking(data) {
    editBookingModal.hide()

    modalSpinner.show()

    try {
        const response = await fetch(`${baseUrl}editBooking`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if (response.ok) {

            contentEditBookingResult.innerHTML = `
            <div class="modal-body modalResultPayment d-flex justify-content-center align-items-center flex-column" style="background-color: #157347; color: #fff">
                <h4 class="mb-5">Reserva editada!</h4>
                <i class="fa-regular fa-circle-check fa-2xl" style="margin-bottom: 20px;"></i>
            </div>`


            modalResult.show()

            setTimeout(() => { location.reload(true) }, 1000)


        } else {
            alert('Algo salió mal. No se pudo editar la reserva.');
            return
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

// Quita las canchas sin horario disponible seleccionado
async function getFieldForTimeBookings(timeBookings) {

    const options = cancha.options;

    timeBookings.forEach(element => {
        const remove = element.time.includes(horarioDesde.value) && element.time.includes(horarioHasta.value);

        if (remove) {
            for (let i = 0; i < options.length; i++) {
                if (options[i].value == element.id_cancha) {
                    options[i].remove();

                    break;
                }
            }
        } else {
            let exists = false;

            for (let i = 0; i < options.length; i++) {
                if (options[i].value == element.id_cancha) {
                    exists = true;

                    break;
                }
            }

            if (!exists) {
                const newOption = new Option(element.nombre_cancha, element.id_cancha);
                cancha.appendChild(newOption);

            }

        }
    });

    const optionsArray = Array.from(cancha.options);

    optionsArray.sort((a, b) => {
        const valueA = parseFloat(a.value);
        const valueB = parseFloat(b.value);
        return valueA - valueB;
    });

    cancha.innerHTML = '';

    if (optionsArray.length == 1) {
        cancha.setAttribute('disabled', 'true')
        cancha.style.backgroundColor = '#bb2d3b'
        optionsArray[0].innerText = 'No hay canchas disponibles en este horario'
    } else {
        cancha.removeAttribute('disabled')
        cancha.style.backgroundColor = ''
        optionsArray[0].innerText = 'Canchas disponibles'
    }

    optionsArray.forEach(option => {
        cancha.appendChild(option);
    });
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