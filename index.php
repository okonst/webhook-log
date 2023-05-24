<?php
/**
*	Тестирование Webhook
*/

require('vendor/autoload.php');
require('config.php');

use Controllers\DatabaseController;
use Controllers\Ecommerce;

// Обработчик ошибок Whoops (https://github.com/filp/whoops)
$whoops = new \Whoops\Run;
$whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

// База
$db = new DatabaseController;
// контроллер Ecommerce
$ecommerce = new Ecommerce($db);

// Роутер Klein (https://github.com/klein/klein.php)
$router = new \Klein\Klein();


/* ROUTES
----------------------------------------------------*/

// Главная страница
$router->respond('GET', '/', function ($request, $response, $service) use($ecommerce) {
	// view главной страницы
    $service->render('view.php', ['table' => $ecommerce->prepareWebhooksList()]);
});

// Новый webhook Ecommerce
$router->respond(['GET', 'POST'], '/ecommerce', function ($request, $response) use($ecommerce){
	
	// сохраняем новый webhook
    $ecommerce->saveWebhook($request);
    // ответ
    $response->header('Access-Control-Allow-Origin', '*');
    $response->body('OK');
});

// Синхронизация Ecommerce с сервером
$router->respond(['GET'], '/sync-ecommerce', function ($request, $response) use($ecommerce){	
	// запускаем синхронизацию
    $result = $ecommerce->sync();
    // ответ
    $response->header('Content-Type', 'application/json');
    $response->body($result);
});

// Получение новых webhook Ecommerce с сервера для синхронизации
$router->respond(['POST'], '/new-ecommerce', function ($request, $response) use($ecommerce){	
	// запускаем синхронизацию
    $result = $ecommerce->processNewWebhooks();
    // ответ
    $response->header('Content-Type', 'application/json');
    $response->body($result);
});

// Удаление webhook
$router->respond('GET', '/remove', function ($request, $response) use($db) {
	// Удаляем webhook
	$db->deleteWebhook($request->param('id'));
    // редирект на главную страницу
    $response->redirect('/');
});

/*
// Новый рекуррентный (повторный) платеж
$router->respond(['GET', 'POST'], '/recurring', function ($request, $response) use($robokassa) {
    // проверяем корректность платежа
    $valid = $robokassa->validateRecurringPayment($request->params());
	if($valid){
		$robokassa->newPayment($request->params());
		$response->body('Recurring payment initiated');
	}
});

// Обработка платежа
$router->respond('GET', '/handle', function ($request, $response, $service) use($robokassa) {
	// находим платеж и создаем необходимые формы
	list($payment, $statusForm, $webhookForm) = $robokassa->handlePayment($request->param('InvId'));
    // view страницы обработки платежа
    $service->render('view.php', [
    	'table' => $robokassa->getTable(),
    	'payment' => $payment,
    	'statusForm' => $statusForm,
    	'webhookForm' => $webhookForm,
    ]);
});

// Апдейт статуса платежа
$router->respond('GET', '/update', function ($request, $response) use($robokassa) {
	// апдейтим статус
	$robokassa->updatePaymentStatus($request->param('InvId'), $request->param('status'));
    // редирект на страницу обработки платежа
    $response->redirect('/handle?InvId='.$request->param('InvId'));
});

// Запрос к вебсервису о состоянии платежа
$router->respond(['GET', 'POST'], '/service/OpState', function ($request, $response) use($robokassa) {
	// обрабатываем запрос
	$xml = $robokassa->handleServiceOpState($request->params());
	// отправляем xml ответ
	$response->header('Content-type', 'text/xml; charset=utf-8');
	$response->body($xml);
});
*/

// 404
$router->onHttpError(function ($code, $router) {
    if ($code == 404) $router->response()->body("Webhook-log: Page not found (error 404).");
});


$router->dispatch();

