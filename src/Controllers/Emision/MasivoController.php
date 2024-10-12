<?php

namespace OpenDTE\Controllers\Emision;

use OpenDTE\Services\HelperService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MasivoController {
    public function __invoke(Request $req, Response $res) : Response {
        $callback = function ($body) {
            $folios = $body["Folios"];
            $folios_data = [];

            foreach ($folios as $key => $value) {
                $folios_data[$key]["data"] = $value["data"];
            }

            $emisor = $body["Emisor"];
            $caratula = [
                'RutReceptor' => $body["RutReceptor"],
                'FchResol' => $body["FchResol"],
                'NroResol' => $body["NroResol"],
            ];

            $documentos = $body["Documentos"];

            // Agrega Emisor y Receptor a cada documento
            foreach ($documentos as $key => $set) {
                $documentos[$key]["Encabezado"]["Emisor"] = $emisor;
            }

            return array("Caratula" => $caratula, "Documento" => $documentos, "ListaFolios" => $folios_data);
        };

        return HelperService::peticion_dte($callback, $req, $res);
    }
}