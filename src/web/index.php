<?php

use sinri\ark\web\ArkRouter;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsJson;

require_once __DIR__.'/../bootstrap.php';

$webService=Ark()->webService();
$webService->setLogger(Ark()->logger("web"));

$router=$webService->getRouter();
$router->setLogger(Ark()->logger("router"));

$router->setErrorHandler(new ArkRouteErrorHandlerAsJson());

$filters=Ark()->readConfig(['web','filters'],[]);

$router->loadAllControllersInDirectoryAsCI(
    __DIR__.'/controller',
    'api/',
    'sinri\NaiveJob\web\controller\\',
    $filters
);

$router->any("",function (){
    header("Location: ./frontend/index.html");
});

$webService->handleRequestForWeb();

// http://localhost/phpstorm/NaiveJob/src/web/frontend/index.html