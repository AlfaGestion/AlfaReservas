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
