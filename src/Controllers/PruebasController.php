<?php

namespace OpenDTE\Controllers;

use OpenDTE\Services\HelperService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PruebasController {
    public function __invoke(Request $req, Response $res) : Response {
        $callback = function ($body) {
            $set = HelperService::obtener_dato_base64($body["Set"], "UNKNOWN");
            $folios = $body["Folios"];
            $folios_primer = [];
            $folios_data = [];

            foreach ($folios as $key => $value) {
                $folios_primer[$key] = $value["primer"];
                $folios_data[$key]["data"] = $value["data"];
            }

            $emisor = $body["Emisor"];
            $receptor = $body["Receptor"];
            $caratula = [
                'RutReceptor' => $body["Receptor"]["RUTRecep"],
                'FchResol' => $body["FchResol"],
                'NroResol' => $body["NroResol"],
            ];

            $setJson = \sasco\LibreDTE\Sii\Certificacion\SetPruebas::getJSON($set["data"], $folios_primer);
            $setArray = json_decode(json_encode(json_decode($setJson)), true);

            // Agrega Emisor y Receptor a cada documento
            foreach ($setArray as $key => $set) {
                $setArray[$key]["Encabezado"]["Emisor"] = $emisor;
                $setArray[$key]["Encabezado"]["Receptor"] = $receptor;
            }

            return array("Caratula" => $caratula, "Documento" => $setArray, "ListaFolios" => $folios_data);
        };

        return HelperService::peticion_dte($callback, $req, $res);
    }
}