<?php
header("Content-Type: application/json");

require "vendor/autoload.php";

include 'inc.php';

use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$app = AppFactory::create();

$app->addErrorMiddleware(true, false, false);
$app->addBodyParsingMiddleware();

$app->group("/dte", function (RouteCollectorProxy $group) {

    $group->post("/consultar", OpenDTE\Controllers\ConsultarController::class);

    $group->post("/emitir/masivo", OpenDTE\Controllers\Emision\MasivoController::class);
    $group->post("/emitir/boleta", OpenDTE\Controllers\Emision\BoletaController::class);
    $group->post("/emitir/factura", OpenDTE\Controllers\Emision\FacturaAfectaController::class);
    $group->post("/emitir/factura-exenta", OpenDTE\Controllers\Emision\FacturaExentaController::class);
    $group->post("/emitir/guia-despacho", OpenDTE\Controllers\Emision\GuiaDespachoController::class);
    $group->post("/emitir/nota-credito", OpenDTE\Controllers\Emision\NotaCreditoController::class);
    $group->post("/emitir/nota-debito", OpenDTE\Controllers\Emision\NotaDebitoController::class);

    $group->post("/libros/guia-despacho", OpenDTE\Controllers\Libros\GuiaDespachoController::class);
    $group->post("/libros/compra-venta", OpenDTE\Controllers\Libros\CompraVentaController::class);

    $group->post("/pruebas", OpenDTE\Controllers\PruebasController::class);
});

$app->run();
