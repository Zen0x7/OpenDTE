<?php

namespace OpenDTE\Controllers;

use OpenDTE\Exceptions\GenericException;
use OpenDTE\Services\HelperService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ConsultarController {

    /**
     * @param Request $req
     * @param Response $res
     * @return Response
     * @throws GenericException
     */
    public function __invoke(Request $req, Response $res) : Response {
        $body = $req->getParsedBody();
        $query = $req->getQueryParams();
        $es_certificacion = HelperService::get_from_query_params("certificacion", 0, $query);

        HelperService::establecer_ambiente($es_certificacion);

        // Decodifica la firma que viene en base64
        $firma = HelperService::get_as_base64($body["Firma"], "FIRMA_NO_BASE64");

        // Extrae el RUT sin el digito verificador
        $rut = substr($body["rut"], 0, -1);

        // Extrae solo el dígito verificador
        $dv = substr($body["rut"], -1);

        $trackId = $body["trackId"];

        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($firma);
        $estado = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$rut, $dv, $trackId, $token]);

        // Si el estado se pudo recuperar se muestra estado y glosa (json)
        if ($estado !== false) {
            $response = json_encode([
                'HDR' => $estado->xpath('/SII:RESPUESTA/SII:RESP_HDR'),
                'BODY' => $estado->xpath('/SII:RESPUESTA/SII:RESP_BODY')
            ]);

            $res->getBody()->write($response);
        }

        // Mostrar error si hubo
        foreach (\sasco\LibreDTE\Log::readAll() as $error)
            echo $error, "\n";

        return $res;
    }
}