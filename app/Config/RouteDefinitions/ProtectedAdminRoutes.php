<?php

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
    $routes->post('deleteField/(:any)', 'Superadmin::deleteField/$1');
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
    $routes->post('saveClientSetupAjax', 'Superadmin::saveClientSetupAjax');
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
