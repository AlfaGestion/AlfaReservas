const checkCustomersWithOffer = document.getElementById('checkCustomersWithOffer')

checkCustomersWithOffer.addEventListener('change', async (e) => {
    if (checkCustomersWithOffer.checked) {
        await getCustomersWithOffer()
    }
})

document.addEventListener('click', async (e) => {
    if (e.target) {
        if (e.target.id == 'searchCustomerButton') {
            checkCustomersWithOffer.checked = false
            const customerPhone = document.getElementById('searchCustomerInput')
            let customers

            if (customerPhone.value == '') {
                customers = await searchCustomer(`${baseUrl}customers/getCustomers`)
            } else {
                customers = await searchCustomer(`${baseUrl}customers/getCustomer/${customerPhone.value}`)
            }
        } else if (e.target.id == 'setOfferTrue') {
            setOfferTrue(true)
            
        } else if (e.target.id == 'setOfferFalse') {
            setOfferFalse(false)
        }
    }
})

async function setOfferTrue(data) {
    try {
        const response = await fetch(`${baseUrl}customers/setOfferTrue`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if(response.ok){
            alert('Operación exitosa')
        } else {
            alert('Ocurrió un error y no se pudo actualizar el valor')
        }

        location.reload(true)

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function setOfferFalse(data) {
    try {
        const response = await fetch(`${baseUrl}customers/setOfferFalse`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        if(response.ok){
            alert('Operación exitosa')
        } else {
            alert('Ocurrió un error y no se pudo actualizar el valor')
        }
        
        location.reload(true)

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}


async function getCustomersWithOffer() {
    try {
        const response = await fetch(`${baseUrl}customers/getCustomersWithOffer`);

        const responseData = await response.json();

        fillCustomersTable(responseData.data)

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function searchCustomer(url) {
    try {
        const response = await fetch(url);

        const responseData = await response.json();

        if (responseData.data != '') {

            fillCustomersTable(responseData.data)

        } else {
            alert('Algo salió mal. No se pudo obtener la información.');
        }

    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

async function fillCustomersTable(data) {
    const customersDiv = document.getElementById('customersDiv')
    let tr = ''
    let actions = ''

    if (Array.isArray(data)) {


        data.forEach(customer => {
            let offer = ''
            customer.offer == 1 ? offer = 'Si' : offer = 'No'

            actions = `
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Acciones
                </button>
                <ul class="dropdown-menu">
                    <li><a type="button" href="${webBaseUrl}customers/editWindow/${customer.id}) ?>" class="btn btn-primary dropdown-item" id="" data-id="${customer.id}">Editar cliente</a></li>
                    <li><a type="button" href="${webBaseUrl}customers/deleteCustomer/${customer.id}}) ?>" class="btn btn-primary dropdown-item" id="" data-id="${customer.id}">Eliminar cliente</a></li>
                </ul>
            </div>
            `

            tr += `
            <tr>
                <td>${customer.name}</td>
                <td>${customer.last_name}</td>
                <td>${customer.dni}</td>
                <td>${customer.phone}</td>
                <td>${customer.city}</td>
                <td>${offer}</td>
                <td>${customer.quantity}</td>
                <td>${actions}</td>
            </tr>
            `
        })
    } else if (typeof data === 'object') {

        let offer = '';
        data.offer == 1 ? offer = 'Si' : offer = 'No';

        actions = `
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Acciones
                </button>
                <ul class="dropdown-menu">
                    <li><a type="button" href="${webBaseUrl}customers/editWindow/${data.id}) ?>" class="btn btn-primary dropdown-item" id="" data-id="${data.id}">Editar cliente</a></li>
                    <li><a type="button" href="${webBaseUrl}customers/deleteCustomer/${data.id}}) ?>" class="btn btn-primary dropdown-item" id="" data-id="${data.id}">Eliminar cliente</a></li>
                </ul>
            </div>
            `

        tr += `
            <tr>
                <td>${data.name}</td>
                <td>${data.last_name}</td>
                <td>${data.dni}</td>
                <td>${data.phone}</td>
                <td>${data.city}</td>
                <td>${offer}</td>
                <td>${data.quantity}</td>
                <td>${actions}</td>
            </tr>
            `
    } else {
        console.error('El parámetro data no es un formato válido.');
        return;
    }

    customersDiv.innerHTML = tr

}
