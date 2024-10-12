<?php

namespace OpenDTE\Controllers\Emision;

use OpenDTE\Services\HelperService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FacturaAfectaController {

    /**
     * @param Request $req
     * @param Response $res
     * @return Response
     */
    public function __invoke(Request $req, Response $res) : Response {
        $callback = function ($body) {
            $caratula = [
                'RutReceptor' => $body["Receptor"]["RUTRecep"],
                'FchResol' => $body["FchResol"],
                'NroResol' => $body["NroResol"],
            ];

            $documento = [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $body["Folio"],
                    ],
                    'Emisor' => $body["Emisor"],
                    'Receptor' => $body["Receptor"],
                ],
                'Detalle' => $body["Detalle"],
            ];

            return array("Caratula" => $caratula, "Documento" => $documento);
        };

        return HelperService::peticion_dte($callback, $req, $res);
    }
}