<?php

// Tenant public routes by numeric code.
$routes->get('([0-9]{9})', 'Home::tenant/$1');

// Pedidos public and admin routes by numeric code.
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

// SaaS admin web routes by tenant slug/base.
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

// Legacy compatibility routes under /comida/*.
$routes->get('comida/([0-9]{9})', 'Comida::index/$1');
$routes->post('comida/([0-9]{9})/reservar', 'Comida::reservar/$1');
$routes->get('comida/([0-9]{9})/pedido/(:segment)', 'Comida::seguimiento/$1/$2');
$routes->post('comida/([0-9]{9})/pedido/(:segment)/recibido', 'Comida::confirmarRecibido/$1/$2');
$routes->get('comida/([0-9]{9})/admin/login', 'PedidosAdmin::login/$1');
$routes->post('comida/([0-9]{9})/admin/login', 'PedidosAdmin::doLogin/$1');
$routes->get('comida/([0-9]{9})/admin/logout', 'PedidosAdmin::logout/$1');
$routes->get('comida/([0-9]{9})/admin', 'PedidosAdmin::index/$1');
$routes->post('comida/([0-9]{9})/admin/catalogo', 'PedidosAdmin::saveCatalogo/$1');
