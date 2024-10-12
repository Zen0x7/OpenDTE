<?php

namespace OpenDTE\Services;

use OpenDTE\Exceptions\GenericException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HelperService
{
    /**
     * Establece el ambiente y el servidor que será usado (maullin y palena)
     * @param es_certificacion cuando el valor es true la librería será usada en modo certificación
     */
    public static function establecer_ambiente($es_certification)
    {
        if ($es_certification) {
            \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::CERTIFICACION);
            \sasco\LibreDTE\Sii::setServidor('maullin');
        } else {
            \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::PRODUCCION);
            \sasco\LibreDTE\Sii::setServidor('palena');
        }
    }

    /**
     * Convierte el contenido de un csv a un array de PHP
     *
     * @param contenido contenido del csv en texto
     * @param delimitador caracter usado como delimitador de cada valor
     * @param escape caracter usado para escapar los caracteres especiales
     * @param enclosure valor usado para delimitar las cadenas de texto
     */
    public static function texto_csv_array($contenido, $delimitador = ';', $escape = '\\', $enclosure = '"')
    {
        $lineas = array();
        $campos = array();

        if ($escape == $enclosure) {
            $escape = '\\';
            $contenido = str_replace(
                array('\\', $enclosure . $enclosure, "\r\n", "\r"),
                array('\\\\', $escape . $enclosure, "\\n", "\\n"),
                $contenido
            );
        } else
            $contenido = str_replace(array("\r\n", "\r"), array("\\n", "\\n"), $contenido);

        $nb = strlen($contenido);
        $campo = '';
        $enEnclosure = false;
        $anterior = '';

        for ($i = 0; $i < $nb; $i++) {
            $c = $contenido[$i];
            if ($c === $enclosure) {
                if ($anterior !== $escape)
                    $enEnclosure ^= true;
                else
                    $campo .= $enclosure;
            } else if ($c === $escape) {
                $next = $contenido[$i + 1];
                if ($next != $enclosure && $next != $escape)
                    $campo .= $escape;
            } else if ($c === $delimitador) {
                if ($enEnclosure)
                    $campo .= $delimitador;
                else {
                    $campos[] = $campo;
                    $campo = '';
                }
            } else if ($c === "\n") {
                $campos[] = $campo;
                $campo = '';
                $lineas[] = $campos;
                $campos = array();
            } else
                $campo .= $c;
            $anterior = $c;
        }
        return $lineas;
    }

    /**
     * Realiza una petición (emisión) de un libro de compra y venta al SII
     * @param data_callback callback usado para parsear los valores enviados
     * en el cuerpo de la petición.
     * @param req Valores de Request
     * @param res Valores de Response
     */
    public static function peticion_libro_compra_venta($data_callback, Request $req, Response $res)
    {
        $body = $req->getParsedBody();
        $query = $req->getQueryParams();
        $es_certificacion = static::get_from_query_params("certificacion", 0, $query);
        $csv_delimitador = static::get_from_query_params("csv_delimitador", ";", $query);
        $tipoOperacion = strtoupper($body["TipoOperacion"]);

        static::establecer_ambiente($es_certificacion);

        $firma = $body["Firma"];

        $data = $data_callback($body);

        $libroDecodificado = base64_decode($data["Libro"]);

        $archivoParseado = static::texto_csv_array($libroDecodificado, $csv_delimitador);

        $result = DTEService::enviar_libro_compra_venta($firma, $data["Caratula"], $archivoParseado, $tipoOperacion, $query);

        $res->getBody()->write($result);

        return $res;
    }

    /**
     * Realiza una petición (emisión) de un libro de guias de despacho al SII
     * @param data_callback callback usado para parsear los valores enviados
     * en el cuerpo de la petición.
     * @param req Valores de Request
     * @param res Valores de Response
     */
    public static function peticion_libro_guia($data_callback, Request $req, Response $res)
    {
        $body = $req->getParsedBody();
        $query = $req->getQueryParams();
        $es_certificacion = static::get_from_query_params("certificacion", 0, $query);

        static::establecer_ambiente($es_certificacion);

        $firma = $body["Firma"];

        $data = $data_callback($body);

        $result = DTEService::enviar_libro_guia($firma, $data["Caratula"], $data["Documento"]);

        $res->getBody()->write($result);

        return $res;
    }

    /**
     * Realiza una petición (emisión/previsualización) de un documento al SII
     * @param data_callback callback usado para parsear los valores enviados
     * en el cuerpo de la petición.
     * @param req Valores de Request
     * @param res Valores de Response
     */
    public static function peticion_dte($data_callback, Request $req, Response $res)
    {
        $body = $req->getParsedBody();
        $query = $req->getQueryParams();
        $es_certificacion = static::get_from_query_params("certificacion", 0, $query);

        static::establecer_ambiente($es_certificacion);

        $firma = $body["Firma"];
        $folios = $body["Folios"];
        $logoUrl = null;
        if (array_key_exists("LogoUrl", $body)) {
            $logoUrl = $body["LogoUrl"];
        }

        $data = $data_callback($body);

        if (array_key_exists("ListaFolios", $data)) {
            $folios = $data["ListaFolios"];
        }

        $result = DTEService::generar_documento($firma, $folios, $data["Caratula"], $data["Documento"], $logoUrl, $query);

        $res->getBody()->write($result);

        return $res;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @param array $query
     * @return mixed
     */
    public static function get_from_query_params(string $key, mixed $default, array $query) : mixed
    {
        return array_key_exists($key, $query) ? $query[$key] : $default;
    }

    /**
     * E
     *
     * @param array $data
     * @param string $default_error
     * @return array
     * @throws GenericException
     */
    public static function get_as_base64(array $data, string $default_error = "Something went wrong") : array
    {
        if (!static::is_base64($data["data"]))
            throw new GenericException("Trying to decode a non base64 value ... in ... $default_error");

        return static::decode_base64($data);
    }

    /**
     * Decode base64 array input
     *
     * @param array $input
     * @return array
     * @throws GenericException
     */
    private static function decode_base64(array $input) : array
    {
        if (!array_key_exists("data", $input))
            throw new GenericException("Trying to decode a missing array attribute ...");

        $input["data"] = base64_decode($input["data"]);
        return $input;
    }

    /**
     * Verify is string is base64
     *
     * @param string $input
     * @return boolean
     */
    public static function is_base64(string $input) : bool
    {
        return $input && preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $input);
    }

    /**
     * @param string $input
     * @return bool
     */
    public static function is_valid_date(string $input) : bool {
        return !preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $input);
    }
}