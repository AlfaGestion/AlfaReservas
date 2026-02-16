const checkCustomersWithOffer = document.getElementById('checkCustomersWithOffer')
const customersTabButton = document.getElementById('nav-customers-tab')

let customersTabLoaded = false

async function loadInitialCustomers() {
    await searchCustomer(`${baseUrl}customers/getCustomers?limit=50`)
}

if (customersTabButton) {
    customersTabButton.addEventListener('shown.bs.tab', async () => {
        if (customersTabLoaded) return
        customersTabLoaded = true
        await loadInitialCustomers()
    })
}

checkCustomersWithOffer.addEventListener('change', async () => {
    if (checkCustomersWithOffer.checked) {
        await getCustomersWithOffer()
    } else {
        await refreshCustomersList()
    }
})

document.addEventListener('click', async (e) => {
    if (e.target) {
        if (e.target.id == 'searchCustomerButton') {
            checkCustomersWithOffer.checked = false
            const customerPhone = document.getElementById('searchCustomerInput')

            if (customerPhone.value == '') {
                await searchCustomer(`${baseUrl}customers/getCustomers?limit=50`)
            } else {
                await searchCustomer(`${baseUrl}customers/getCustomer/${customerPhone.value}`)
            }
        } else if (e.target.id == 'setOfferTrue') {
            await setOfferTrue(true)
        } else if (e.target.id == 'setOfferFalse') {
            await setOfferFalse(false)
        }
    }
})

async function refreshCustomersList() {
    const customerPhone = document.getElementById('searchCustomerInput')
    const phoneValue = customerPhone ? customerPhone.value.trim() : ''

    if (checkCustomersWithOffer && checkCustomersWithOffer.checked) {
        await getCustomersWithOffer()
        return
    }

    if (phoneValue !== '') {
        await searchCustomer(`${baseUrl}customers/getCustomer/${phoneValue}`)
        return
    }

    await searchCustomer(`${baseUrl}customers/getCustomers?limit=50`)
}

async function setOfferTrue(data) {
    try {
        const response = await fetch(`${baseUrl}customers/setOfferTrue`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })

        if (response.ok) {
            alert('Oferta asignada correctamente.')
            await refreshCustomersList()
        } else {
            alert('No se pudo completar la operación. Intenta nuevamente.')
        }
    } catch (error) {
        console.error('Error:', error)
        throw error
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
        })

        if (response.ok) {
            alert('Oferta quitada correctamente.')
            await refreshCustomersList()
        } else {
            alert('No se pudo completar la operación. Intenta nuevamente.')
        }
    } catch (error) {
        console.error('Error:', error)
        throw error
    }
}

async function getCustomersWithOffer() {
    try {
        const response = await fetch(`${baseUrl}customers/getCustomersWithOffer`)
        const responseData = await response.json()

        fillCustomersTable(responseData.data)
    } catch (error) {
        console.error('Error:', error)
        throw error
    }
}

async function searchCustomer(url) {
    try {
        const response = await fetch(url)
        const responseData = await response.json()

        if (responseData.data != '') {
            fillCustomersTable(responseData.data)
        } else {
            alert('No se pudo obtener la información. Intenta nuevamente.')
        }
    } catch (error) {
        console.error('Error:', error)
        throw error
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
        let offer = ''
        data.offer == 1 ? offer = 'Si' : offer = 'No'

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
        console.error('El parametro data no es un formato valido.')
        return
    }

    customersDiv.innerHTML = tr
}
