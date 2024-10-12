<?php

namespace OpenDTE\Controllers\Emision;

use OpenDTE\Services\HelperService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotaCreditoController {
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
                        'TipoDTE' => 61,
                        'Folio' => $body["Folio"],
                    ],
                    'Emisor' => $body["Emisor"],
                    'Receptor' => $body["Receptor"],
                    'Totales' => [
                        'MntNeto' => 0,
                        'MntExe' => 0,
                        'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                        'IVA' => 0,
                        'MntTotal' => 0,
                    ],
                ],
                'Detalle' => $body["Detalle"],
                'Referencia' => $body["Referencia"]
            ];

            return array("Caratula" => $caratula, "Documento" => $documento);
        };

        return HelperService::peticion_dte($callback, $req, $res);
    }
}