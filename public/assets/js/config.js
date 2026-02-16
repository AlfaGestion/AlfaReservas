const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
const apiBaseUrlMeta = document.querySelector('meta[name="app-base-url"]')
const webBaseUrlMeta = document.querySelector('meta[name="app-web-base-url"]')
//const apiBaseUrl = apiBaseUrlMeta?.content ? apiBaseUrlMeta.content : (isLocalhost ? 'http://localhost:8080/' : 'https://alfagestion.com.ar/cancha-test/')
//const apiBaseUrl = apiBaseUrlMeta?.content ? apiBaseUrlMeta.content : (isLocalhost ? 'https://audrina-unexpectable-swaggeringly.ngrok-free.dev/' : 'https://alfagestion.com.ar/cancha-test/')
const apiBaseUrl = apiBaseUrlMeta?.content ? apiBaseUrlMeta.content : (isLocalhost ? 'https://audrina-unexpectable-swaggeringly.ngrok-free.dev/' : 'https://alfagestion.com.ar/cancha_pruebas/')

const webBaseUrl = webBaseUrlMeta?.content ? webBaseUrlMeta.content : `${window.location.origin}/`
const baseUrl = apiBaseUrl
//const publicKeyMP = "APP_USR-aac9eac0-3383-456a-b41d-a591b19d4962"
const publicKeyMpEl = document.getElementById('publicKeyMp')
const publicKeyMp = publicKeyMpEl ? publicKeyMpEl.value : ''

// Fallback anti-cache: si algun JS viejo muestra mensaje generico,
// lo reemplazamos por uno especifico segun la accion ejecutada.
let lastClickedActionId = ''
document.addEventListener('click', (e) => {
    const t = e.target
    if (!t || !t.id) return
    lastClickedActionId = t.id
})

const originalAlert = window.alert.bind(window)
let appAlertModal = null
let appAlertModalBody = null

function ensureAppAlertModal() {
    if (appAlertModal && appAlertModalBody) return true
    if (typeof bootstrap === 'undefined') return false

    const wrapper = document.createElement('div')
    wrapper.innerHTML = `
        <div class="modal fade" id="appAlertModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Aviso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" id="appAlertModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                    </div>
                </div>
            </div>
        </div>
    `
    document.body.appendChild(wrapper.firstElementChild)

    const modalEl = document.getElementById('appAlertModal')
    appAlertModalBody = document.getElementById('appAlertModalBody')
    appAlertModal = new bootstrap.Modal(modalEl)
    return true
}

function showAppAlert(text) {
    if (!ensureAppAlertModal()) {
        originalAlert(text)
        return
    }
    appAlertModalBody.textContent = text
    appAlertModal.show()
}

window.alert = function patchedAlert(message) {
    let text = String(message || '')
    const isGenericSuccess = text === 'Operacion realizada correctamente.' || text === 'Operación realizada correctamente.' || text === 'OperaciÃ³n realizada correctamente.'

    if (isGenericSuccess) {
        if (lastClickedActionId === 'confirmCancelReservations') {
            text = 'Cierre de cancha informado correctamente.'
        } else if (lastClickedActionId === 'setOfferTrue') {
            text = 'Oferta asignada correctamente.'
        } else if (lastClickedActionId === 'setOfferFalse') {
            text = 'Oferta removida correctamente.'
        } else if (lastClickedActionId === 'saveOfferRate') {
            text = 'Oferta actualizada correctamente.'
        } else if (lastClickedActionId === 'saveRate') {
            text = 'Porcentaje actualizado correctamente.'
        } else {
            text = 'Operacion completada correctamente.'
        }
    }

    return showAppAlert(text)
}
