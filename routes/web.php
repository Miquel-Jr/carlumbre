<?php

$router->get('/', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/dashboard', 'DashboardController@index');

$router->get('/clients', 'ClientController@index');

$router->get('/products', 'ProductController@index');

$router->get('/clients/create', 'ClientController@create');
$router->post('/clients/store', 'ClientController@store');

$router->get('/clients/edit', 'ClientController@edit');
$router->post('/clients/update', 'ClientController@update');

$router->get('/clients/delete', 'ClientController@delete');

$router->get('/clients/cars', 'CarController@index');
$router->get('/clients/cars/create', 'CarController@create');
$router->post('/clients/cars/store', 'CarController@store');
$router->get('/clients/cars/edit', 'CarController@edit');
$router->post('/clients/cars/update', 'CarController@update');
$router->get('/clients/cars/delete', 'CarController@delete');
$router->get('/clients/cars/delete-photo', 'CarPhotoController@deletePhoto');

$router->get('/services', 'ServiceController@index');
$router->get('/services/create', 'ServiceController@create');
$router->post('/services/store', 'ServiceController@store');
$router->get('/services/edit', 'ServiceController@edit');
$router->post('/services/update', 'ServiceController@update');
$router->get('/services/delete', 'ServiceController@delete');
$router->post('/services/whatsapp', 'WhatsappController@send');

$router->get('/quotes', 'QuoteController@index');
$router->get('/quotes/create', 'QuoteController@create');
$router->post('/quotes/store', 'QuoteController@store');
$router->get('/quotes/edit', 'QuoteController@edit');
$router->post('/quotes/update', 'QuoteController@update');
$router->get('/quotes/delete', 'QuoteController@delete');
$router->get('/quotes/approve', 'QuoteController@approve');
$router->get('/quotes/create-work-order', 'QuoteController@createWorkOrder');
$router->get('/quotes/reject', 'QuoteController@reject');
$router->get('/quotes/pdf', 'QuoteController@generatePdf');

$router->get('/work-orders', 'WorkOrderController@index');
$router->get('/work-orders/show', 'WorkOrderController@show');
$router->get('/work-orders/delete', 'WorkOrderController@delete');
$router->post('/work-orders/add-activity', 'WorkOrderController@addActivity');
$router->post('/work-orders/update-activity-status', 'WorkOrderController@updateActivityStatus');

$router->get('/billing', 'BillingController@index');
$router->get('/billing/show', 'BillingController@show');
$router->post('/billing/generate', 'BillingController@generate');
$router->post('/billing/update-invoice-number', 'BillingController@updateInvoiceNumber');
$router->post('/billing/update-status', 'BillingController@updateStatus');
$router->get('/billing/delete', 'BillingController@delete');

$router->get('/notifications', 'NotificationController@index');
$router->get('/notifications/resend', 'NotificationController@resend');
$router->get('/notifications/delete', 'NotificationController@delete');
