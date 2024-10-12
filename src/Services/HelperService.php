<?php

namespace OpenDTE\Services;

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
        $es_certificacion = static::obtener_dato_de_query("certificacion", 0, $query);
        $csv_delimitador = static::obtener_dato_de_query("csv_delimitador", ";", $query);
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
        $es_certificacion = static::obtener_dato_de_query("certificacion", 0, $query);

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
        $es_certificacion = static::obtener_dato_de_query("certificacion", 0, $query);

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
     * Obtiene el valor desde un array, si el valor no existe
     * en el array, se devolverá el valor de porefecto
     * @param key llave a buscar en el array
     * @param default valor a devolver si la llave no existe
     * @return value valor encontrado o valor por defecto
     */
    public static function obtener_dato_de_query($key, $default, $query)
    {
        if (array_key_exists($key, $query)) {
            return $query[$key];
        }
        return $default;
    }

    /**
     * Verifica si el array enviado contiene un campo "data" codificado
     * en base64 y mueestra un error si no lo es. Al encontrarlo lo
     * decodifica y lo devuelve.
     * @param dato Array con el campo "data"
     * @param errorName Nombre del error a mostrar (en caso de haber uno)
     * @return array información decodificada
     */
    public static function obtener_dato_base64($dato, $errorName)
    {
        if (!static::es_base64($dato["data"])) {
            die(ErrorService::get_error($errorName));
        }

        return static::decodificar_dato64($dato);
    }

    /**
     * Decodifica el campo "data" enviado en el array $codificado
     * @param codificado array con la información codificada
     * @return array información decodificada
     */
    public static function decodificar_dato64($codificado)
    {
        $codificado["data"] = base64_decode($codificado["data"]);
        return $codificado;
    }

    /**
     * Verifica que el string enviado esté codificado en base64
     * @param texto texto codificado a verificar
     * @return boolean true si el texto está codificado en base64
     */
    public static function es_base64($texto)
    {
        return $texto and (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $texto);
    }
}