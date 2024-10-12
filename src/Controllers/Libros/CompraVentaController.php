<?php

namespace OpenDTE\Controllers\Libros;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CompraVentaController
{
    public function __invoke(Request $req, Response $res) : Response {
        $callback = function ($body) {
            $caratula = [
                'RutEmisorLibro' => $body["RutEmisorLibro"],
                'RutEnvia' => $body["RutEnvia"],
                'PeriodoTributario' => $body["PeriodoTributario"],
                'FchResol' => $body["FchResol"],
                'NroResol' => $body["NroResol"],
                'TipoOperacion' => $body["TipoOperacion"],
                'TipoLibro' => $body["TipoLibro"],
                'TipoEnvio' => $body["TipoEnvio"],
                'FolioNotificacion' => $body["FolioNotificacion"],
            ];

            return array("Caratula" => $caratula, "Libro" => $body["Libro"]);
        };

        return peticion_libro_compra_venta($callback, $req, $res);
    }
}