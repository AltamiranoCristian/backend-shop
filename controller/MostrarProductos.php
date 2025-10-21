<?php
require_once "../models/Producto.php"; // Incluir el modelo de Producto
require_once '../models/Linea.php';
require_once '../models/Tipo.php';
require_once "../utils/ErroresAplic.php"; // Incluir clase de manejo de errores
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
$nErr = -1; // Inicializar variable de error
$oProducto = new Producto(); // Instanciar objeto Producto
$arrProductos = []; // Inicializar arreglo para productos encontrados
$arrLineas = []; // Inicializar arreglo para líneas
$arrTipos = []; 
$arrSabores = [];
//modificacion: se quitó una diagonal entre serverport y phpself
$subURL = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER ["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"));

$subURL = substr($subURL, 0, strrpos($subURL, "/")). "/images/";

try {
    // Llamar al método buscarTodos del modelo
    $arrProductos = $oProducto->buscarTodos();
    $arrLineas = Linea::buscarTodos();
    $arrTipos = Tipo::buscarTodos();
    $arrSabores = Sabor::buscarTodos();
} catch (Exception $e) {
    // Enviar el error específico a la bitácora de PHP
    error_log($e->getFile() . " " . $e->getLine() . " " . $e->getMessage(), 0);
    $nErr = Errores::ERROR_EN_BD; // Establecer código de error
}

// Verificar si hubo error
if ($nErr == -1) {
    // Construir la respuesta JSON de éxito
    $sJsonRet = '{
        "success": true,
        "status": "ok",
        "data": {
            "Productos": [';
            // Recorrer el arreglo para llenar objetos de producto
        foreach ($arrProductos as $oProducto) {
            $sJsonRet .= '{
                "id": ' . $oProducto->getId() . ', 
                "nombre": "' . $oProducto->getNombre() . '", 
                "descripcion": "' . $oProducto->getDescripcion() . '", 
                "fotografia": "' . $subURL .$oProducto->getFotografia() . '", 
                "precio": ' . $oProducto->getPrecio() . ',
                "linea": "' . $oProducto->getLinea()->getNombre() . '",
                "tipo": "' . $oProducto->getTipo()->getNombre() . '",
                "sabores": [';
                // Obtener los sabores del producto
                $sabores = $oProducto->getSabores();
                foreach ($sabores as $index => $sabor) {
                    $sJsonRet .= '"' . $sabor->getNombre() . '"';
                    // Si no es el último sabor, añade una coma
                    if ($index < count($sabores) - 1) {
                        $sJsonRet .= ',';
                    }
                }
                // Cerrar el array de sabores
                $sJsonRet .= ']
            },';
        }

        // Eliminar la última coma sobrante
        $sJsonRet = substr($sJsonRet, 0, -1);

        // Añadir la información de líneas y tipos
        $sJsonRet .= '],
            "Lineas": [';
            foreach ($arrLineas as $linea) {
                $sJsonRet .= '{
                    "id": ' . $linea->getId() . ', 
                    "nombre": "' . $linea->getNombre() . '"
                },';
            }
            // Eliminar la última coma sobrante
            $sJsonRet = substr($sJsonRet, 0, -1);
            $sJsonRet .= '],
            "Tipos": [';
            foreach ($arrTipos as $tipo) {
                $sJsonRet .= '{
                    "id": ' . $tipo->getId() . ', 
                    "nombre": "' . $tipo->getNombre() . '"
                },';
            }
            // Eliminar la última coma sobrante
            $sJsonRet = substr($sJsonRet, 0, -1);
            $sJsonRet .= '],

            "Sabores": [';
            foreach ($arrSabores as $sabor) {
                $sJsonRet .= '{
                    "id": ' . $sabor->getId() . ', 
                    "nombre": "' . $sabor->getNombre() . '"
                },';
            }
            // Eliminar la última coma sobrante
            $sJsonRet = substr($sJsonRet, 0, -1);
            $sJsonRet .= ']
        }
    }';
} else {
    // Manejar el error y construir la respuesta JSON de error
    $oErr = new Errores();
    $oErr->setError($nErr);
    $sJsonRet = '{
        "success": false,
        "status": "' . $oErr->getTextoError() . '",
        "data": {}
    }';
}
// Retornar JSON a quien hizo la llamada
header('Content-type: application/json');
echo $sJsonRet;