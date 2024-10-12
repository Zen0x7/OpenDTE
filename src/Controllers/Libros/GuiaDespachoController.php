<?php

namespace OpenDTE\Controllers\Libros;

use OpenDTE\Services\HelperService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GuiaDespachoController
{
    public function __invoke(Request $req, Response $res) : Response {
        $callback = function ($body) {
            $caratula = [
                'RutEmisorLibro' => $body["RutEmisorLibro"],
                'FchResol' => $body["FchResol"],
                'NroResol' => $body["NroResol"],
                'FolioNotificacion' => $body["FolioNotificacion"],
            ];

            $documento = [
                'Detalle' => $body["Detalle"]
            ];

            return array("Caratula" => $caratula, "Documento" => $documento);
        };

        return HelperService::peticion_libro_guia($callback, $req, $res);
    }
}