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

$routeDefinitionFiles = [
    APPPATH . 'Config/RouteDefinitions/TenantFoodRoutes.php',
    APPPATH . 'Config/RouteDefinitions/PublicRoutes.php',
    APPPATH . 'Config/RouteDefinitions/AuthRoutes.php',
    APPPATH . 'Config/RouteDefinitions/ProtectedAdminRoutes.php',
];

foreach ($routeDefinitionFiles as $routeDefinitionFile) {
    require $routeDefinitionFile;
}

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
$environment = env('CI_ENVIRONMENT', 'production');

if (is_file(APPPATH . 'Config/' . $environment . '/Routes.php')) {
    require APPPATH . 'Config/' . $environment . '/Routes.php';
}

// Keep the slug catch-all isolated and at the very end so specific/system routes win first.
$routes->get('([A-Za-z0-9_]+)', 'Home::tenantByBase/$1');
