<?php
header('Access-Control-Allow-Origin:*');
require_once "../models/Producto.php"; // Incluir el modelo de Producto
require_once "../utils/ErroresAplic.php"; // Incluir clase de manejo de errores

$nErr = -1; // Inicializar variable de error
$oProducto = new Producto(); // Instanciar objeto Producto
$arrEncontrados = []; // Inicializar arreglo para productos encontrados
$subURL = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER ["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . "/" . substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"));
$subURL = substr($subURL, 0, strrpos($subURL, "/")). "/images/";
if (isset($_REQUEST["clave"]) && !empty($_REQUEST["clave"])){
    //Verifica que sea entero
    if (is_numeric($_REQUEST["clave"])){
        try {
            if (!$oProducto->buscarPorLinea($_REQUEST['clave'])) {
                $nErr = Errores::NO_EXISTE_BUSCADO;
            }else{
                $arrEncontrados = $oProducto->buscarPorLinea($_REQUEST['clave']);
            }
        } catch (Exception $e) {
            // Enviar el error específico a la bitácora de PHP
            error_log($e->getFile() . " " . $e->getLine() . " " . $e->getMessage(), 0);
            $nErr = Errores::ERROR_EN_BD; // Establecer código de error
        }
    }else{
        $nErr = Errores::ERROR_DATOS;
    }
}else{
    $nErr = Errores::FALTAN_DATOS;
}
// Verificar si hubo error
if ($nErr == -1) {
    // Construir la respuesta JSON de éxito
    $sJsonRet = 
    '{
        "success": true,
        "status": "ok",
        "data": {
            "Productos": [';
            // Recorrer el arreglo para llenar objetos de producto
        foreach ($arrEncontrados as $oProducto) {
            $sJsonRet .= '{
                "id": ' . $oProducto->getId() . ', 
                "nombre": "' . $oProducto->getNombre() . '", 
                "descripcion": "' . $oProducto->getDescripcion() . '", 
                "fotografia": "' . $subURL . $oProducto->getFotografia() . '", 
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

// Colocar cierre de arreglo y de objeto
$sJsonRet .= '
        ]
    }
}';
} else {
    // Manejar el error y construir la respuesta JSON de error
    $oErr = new Errores();
    $oErr->setError($nErr);
    $sJsonRet = 
    '{
        "success": false,
        "status": "' . $oErr->getTextoError() . '",
        "data": {}
    }';
}

// Retornar JSON a quien hizo la llamada
header('Content-type: application/json');
echo $sJsonRet;