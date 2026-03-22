<?php

$routes->get('/', 'Auth::index');
$routes->post('/', 'Auth::login');

$routes->post('formInfo', 'Home::infoReserva');
$routes->post('checkClosure', 'Home::checkClosure');
$routes->get('getUpcomingClosure', 'Home::getUpcomingClosure');
$routes->get('getDataMp', 'Home::getDataMp');
$routes->post('deleteRejected', 'Home::deleteRejected');

$routes->post('setPreference', 'MercadoPago::setPreference');
$routes->post('cancelPendingMpReservation', 'MercadoPago::cancelPendingMpReservation');
$routes->post('savePreferenceIds', 'MercadoPago::savePreferenceIds');
$routes->get('payment/success', 'MercadoPago::success');
$routes->get('payment/failure', 'MercadoPago::failure');

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
