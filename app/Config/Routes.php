<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('([0-9]{9})', 'Home::tenant/$1');
$routes->get('pedidos/([0-9]{9})', 'Comida::index/$1');
$routes->post('pedidos/([0-9]{9})/reservar', 'Comida::reservar/$1');
$routes->get('pedidos/([0-9]{9})/pedido/(:segment)', 'Comida::seguimiento/$1/$2');
$routes->post('pedidos/([0-9]{9})/pedido/(:segment)/recibido', 'Comida::confirmarRecibido/$1/$2');
$routes->get('pedidos/([0-9]{9})/admin/login', 'PedidosAdmin::login/$1');
$routes->post('pedidos/([0-9]{9})/admin/login', 'PedidosAdmin::doLogin/$1');
$routes->get('pedidos/([0-9]{9})/admin/logout', 'PedidosAdmin::logout/$1');
$routes->get('pedidos/([0-9]{9})/admin', 'PedidosAdmin::index/$1');
$routes->post('pedidos/([0-9]{9})/admin/catalogo', 'PedidosAdmin::saveCatalogo/$1');
$routes->post('pedidos/([0-9]{9})/admin/general', 'PedidosAdmin::saveGeneralConfig/$1');
$routes->post('pedidos/([0-9]{9})/admin/usuarios', 'PedidosAdmin::saveAdminUser/$1');
$routes->post('pedidos/([0-9]{9})/admin/configuracion', 'PedidosAdmin::saveWebSettings/$1');
$routes->post('pedidos/([0-9]{9})/admin/ofertas', 'PedidosAdmin::saveOfferSettings/$1');
$routes->get('pedidos/([0-9]{9})/admin/pedido/(:num)', 'PedidosAdmin::pedidoDetalle/$1/$2');
$routes->get('pedidos/([0-9]{9})/admin/pedido/(:num)/json', 'PedidosAdmin::pedidoDetalleJson/$1/$2');
$routes->post('pedidos/([0-9]{9})/admin/pedido/(:num)/estado', 'PedidosAdmin::savePedidoEstado/$1/$2');
$routes->post('pedidos/([0-9]{9})/admin/pedido/(:num)/editar', 'PedidosAdmin::savePedidoEdicion/$1/$2');

// Nueva ruta admin web por nombre/base (slug)
$routes->get('([A-Za-z0-9_]+)/adminWeb/login', 'PedidosAdmin::login/$1');
$routes->post('([A-Za-z0-9_]+)/adminWeb/login', 'PedidosAdmin::doLogin/$1');
$routes->get('([A-Za-z0-9_]+)/adminWeb/logout', 'PedidosAdmin::logout/$1');
$routes->get('([A-Za-z0-9_]+)/adminWeb', 'PedidosAdmin::index/$1');
$routes->post('([A-Za-z0-9_]+)/adminWeb/catalogo', 'PedidosAdmin::saveCatalogo/$1');
$routes->post('([A-Za-z0-9_]+)/adminWeb/general', 'PedidosAdmin::saveGeneralConfig/$1');
$routes->post('([A-Za-z0-9_]+)/adminWeb/usuarios', 'PedidosAdmin::saveAdminUser/$1');
$routes->post('([A-Za-z0-9_]+)/adminWeb/configuracion', 'PedidosAdmin::saveWebSettings/$1');
$routes->post('([A-Za-z0-9_]+)/adminWeb/ofertas', 'PedidosAdmin::saveOfferSettings/$1');
$routes->get('([A-Za-z0-9_]+)/pedido/(:segment)', 'Comida::seguimiento/$1/$2');
$routes->post('([A-Za-z0-9_]+)/pedido/(:segment)/recibido', 'Comida::confirmarRecibido/$1/$2');
$routes->get('([A-Za-z0-9_]+)/adminWeb/pedido/(:num)', 'PedidosAdmin::pedidoDetalle/$1/$2');
$routes->get('([A-Za-z0-9_]+)/adminWeb/pedido/(:num)/json', 'PedidosAdmin::pedidoDetalleJson/$1/$2');
$routes->post('([A-Za-z0-9_]+)/adminWeb/pedido/(:num)/estado', 'PedidosAdmin::savePedidoEstado/$1/$2');
$routes->post('([A-Za-z0-9_]+)/adminWeb/pedido/(:num)/editar', 'PedidosAdmin::savePedidoEdicion/$1/$2');

// Compatibilidad legacy: rutas antiguas /comida/*
$routes->get('comida/([0-9]{9})', 'Comida::index/$1');
$routes->post('comida/([0-9]{9})/reservar', 'Comida::reservar/$1');
$routes->get('comida/([0-9]{9})/pedido/(:segment)', 'Comida::seguimiento/$1/$2');
$routes->post('comida/([0-9]{9})/pedido/(:segment)/recibido', 'Comida::confirmarRecibido/$1/$2');
$routes->get('comida/([0-9]{9})/admin/login', 'PedidosAdmin::login/$1');
$routes->post('comida/([0-9]{9})/admin/login', 'PedidosAdmin::doLogin/$1');
$routes->get('comida/([0-9]{9})/admin/logout', 'PedidosAdmin::logout/$1');
$routes->get('comida/([0-9]{9})/admin', 'PedidosAdmin::index/$1');
$routes->post('comida/([0-9]{9})/admin/catalogo', 'PedidosAdmin::saveCatalogo/$1');
$routes->get('/', 'Auth::index');
$routes->post('/', 'Auth::login');
$routes->post('formInfo', 'Home::infoReserva');
$routes->post('checkClosure', 'Home::checkClosure');
$routes->get('getUpcomingClosure', 'Home::getUpcomingClosure');
$routes->get('getDataMp', 'Home::getDataMp');
$routes->get('deleteRejected', 'Home::deleteRejected');
$routes->get('phpinfo', 'Debug::phpinfo');

$routes->post('setPreference', 'MercadoPago::setPreference');
$routes->post('cancelPendingMpReservation', 'MercadoPago::cancelPendingMpReservation');
$routes->post('savePreferenceIds', 'MercadoPago::savePreferenceIds');
$routes->get('payment/success', 'MercadoPago::success');
$routes->get('payment/failure', 'MercadoPago::failure');

$routes->group('auth', function ($routes) {
    $routes->post('register', 'Auth::dbRegister');
    $routes->get('logOut', 'Auth::log_out');
    $routes->get('login', 'Auth::index');
    $routes->post('login', 'Auth::login');
    $routes->get('register', 'Auth::register');
});

$routes->post('saveBooking', 'Bookings::saveBooking');
$routes->get('getBookings/(:any)', 'Bookings::getBookings/$1');
$routes->get('getBooking/(:any)', 'Bookings::getBooking/$1');
$routes->get('bookingPdf/(:any)', 'Bookings::bookingPdf/$1');


$routes->get('getFields', 'Fields::getFields');
$routes->get('getField/(:any)', 'Fields::getField/$1');

$routes->get('getRate', 'Rate::getRate');

$routes->get('getOffersRate', 'Offers::getOffersRate');

$routes->get('getNocturnalTime', 'Time::getNocturnalTime');

$routes->get('customers/register', 'Customers::register');
$routes->post('customers/register', 'Customers::dbRegister');
$routes->get('getCustomer/(:any)', 'Customers::getCustomer/$1');

$routes->get('getUser/(:any)', 'Users::getUser/$1');
$routes->post('editUser', 'Users::editUser');


$routes->group('', ['filter' => 'auth'], function ($routes) {

    $routes->get('upload', 'Upload::index');
    $routes->post('upload/upload', 'Upload::upload');
    $routes->get('deleteBackground', 'Upload::deleteBackground');

    $routes->get('configMpView', 'Superadmin::configMpView');
    $routes->post('configMp', 'Superadmin::configMp');
    $routes->get('abmAdmin', 'Superadmin::index');
    $routes->get('abmRubros', 'Superadmin::index');
    $routes->post('saveField', 'Superadmin::saveField');
    $routes->get('([A-Za-z0-9_]+)/admin', 'Superadmin::index');
    $routes->post('editField/(:any)', 'Superadmin::editField/$1');
    $routes->post('getActiveBookings', 'Superadmin::getActiveBookings');
    $routes->post('getAnnulledBookings', 'Superadmin::getAnnulledBookings');
    $routes->post('checkCancelReservations', 'Superadmin::checkCancelReservations');
    $routes->post('saveCancelReservations', 'Superadmin::saveCancelReservations');
    $routes->post('updateCancelReservation', 'Superadmin::updateCancelReservation');
    $routes->post('getCancelReservations', 'Superadmin::getCancelReservations');
    $routes->post('deleteCancelReservation', 'Superadmin::deleteCancelReservation');
    $routes->post('saveConfigGeneral', 'Superadmin::saveConfigGeneral');
    $routes->post('getClienteEstadoConfigAjax', 'Superadmin::getClienteEstadoConfigAjax');
    $routes->post('saveClienteEstadoConfigAjax', 'Superadmin::saveClienteEstadoConfigAjax');
    $routes->post('saveUserAjax', 'Superadmin::saveUserAjax');
    $routes->post('deleteUser/(:any)', 'Superadmin::deleteUser/$1');
    $routes->post('saveCliente', 'Superadmin::saveCliente');
    $routes->post('toggleClienteStatus/(:num)', 'Superadmin::toggleClienteStatus/$1');
    $routes->post('saveClienteAjax', 'Superadmin::saveClienteAjax');
    $routes->post('toggleClienteStatusAjax', 'Superadmin::toggleClienteStatusAjax');
    $routes->post('saveClientProfileAjax', 'Superadmin::saveClientProfileAjax');
    $routes->post('saveClientLogoAjax', 'Superadmin::saveClientLogoAjax');
    $routes->post('saveOwnClientPasswordAjax', 'Superadmin::saveOwnClientPasswordAjax');
    $routes->post('addClientBaseUserAjax', 'Superadmin::addClientBaseUserAjax');
    $routes->post('saveClientPlanAjax', 'Superadmin::saveClientPlanAjax');
    $routes->post('saveRubro', 'Superadmin::saveRubro');
    $routes->post('savePlan', 'Superadmin::savePlan');
    $routes->post('saveRubroParametro', 'Superadmin::saveRubroParametro');
    $routes->post('savePlanAjax', 'Superadmin::savePlanAjax');
    $routes->post('saveRubroAjax', 'Superadmin::saveRubroAjax');
    $routes->post('saveRubroParametroAjax', 'Superadmin::saveRubroParametroAjax');

    $routes->post('saveTime', 'Time::saveTime');
    $routes->get('getTime', 'Time::getTime');

    $routes->post('confirmMP', 'Bookings::confirmMP');

    $routes->post('completePayment/(:any)', 'Bookings::completePayment/$1');
    $routes->post('getReports', 'Bookings::getReports');
    $routes->post('getMpPayments', 'Bookings::getMpPayments');
    $routes->post('cancelBooking', 'Bookings::cancelBooking');
    $routes->post('editBooking', 'Bookings::editBooking');
    $routes->post('saveAdminBooking', 'Bookings::saveAdminBooking');
    $routes->get('generateReportPdf/(:any)/(:any)/(:any)', 'Bookings::generateReportPdf/$1/$2/$3');
    $routes->get('generatePaymentsReportPdf/(:any)/(:any)', 'Bookings::generatePaymentsReportPdf/$1/$2');

    $routes->post('saveRate', 'Rate::saveRate');

    $routes->post('saveOfferRate', 'Offers::saveOfferRate');

    $routes->group('customers', function ($routes) {
        $routes->get('deleteCustomer/(:any)', 'Customers::delete/$1');
        $routes->post('editCustomer', 'Customers::edit');
        $routes->get('editWindow/(:any)', 'Customers::editWindow/$1');
        $routes->get('getCustomer/(:any)', 'Customers::getCustomer/$1');
        $routes->get('getCustomers', 'Customers::getCustomers');
        $routes->get('getCustomersWithOffer', 'Customers::getCustomersWithOffer');
        $routes->post('setOfferTrue', 'Customers::setOfferTrue');
        $routes->post('setOfferFalse', 'Customers::setOfferFalse');
    });
});


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}

// Ruta tenant por base normalizada, se deja al final para no pisar rutas del sistema.
$routes->get('([A-Za-z0-9_]+)', 'Home::tenantByBase/$1');
