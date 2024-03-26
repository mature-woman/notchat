<?php

declare(strict_types=1);

namespace mirzaev\notchat;

// Files of the project
use mirzaev\notchat\controllers\core as controller,
	mirzaev\notchat\models\core as model;

// Framework for PHP
use mirzaev\minimal\core,
	mirzaev\minimal\router;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

define('VIEWS', realpath('..' . DIRECTORY_SEPARATOR . 'views'));
define('STORAGE', realpath('..' . DIRECTORY_SEPARATOR . 'storage'));
define('INDEX', __DIR__);

// Автозагрузка
require INDEX . DIRECTORY_SEPARATOR
	. '..' . DIRECTORY_SEPARATOR
	. '..' . DIRECTORY_SEPARATOR
	. '..' . DIRECTORY_SEPARATOR
	. '..' . DIRECTORY_SEPARATOR
	. 'vendor' . DIRECTORY_SEPARATOR
	. 'autoload.php';

// Инициализация маршрутизатора
$router = new router;

// Запись маршрутов
$router->write('/', 'index', 'index', 'GET');
$router->write('/', 'index', 'index', 'POST');
$router->write('/manifest', 'index', 'manifest', 'GET');
$router->write('/offline', 'index', 'offline', 'GET');
$router->write('/cache.js', 'index', 'cache', 'GET');
$router->write('/server/read/$server', 'server', 'read', 'POST');
$router->write('/server/write/$server', 'server', 'write', 'POST');
$router->write('/servers', 'index', 'servers', 'POST');

// Инициализация ядра
$core = new core(namespace: __NAMESPACE__, router: $router, controller: new controller(false), model: new model(false));

// Обработка запроса
echo $core->start();
