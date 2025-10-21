<?php
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header("Access-Control-Allow-Headers: tokenauth");

require_once "../models/Administrador.php";
require_once "../models/Producto.php";
require_once "../utils/ErroresAplic.php";
$tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif'];
$operacionesAdmitidas = ["crear", "actualizar", "eliminar"];
//construccion de ruta para la imagen
$subURL = $_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . "/" . substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"));
$subURL = substr($subURL, 0, strrpos($subURL, "/")) . "/images/";
$nErr = -1;
$objProducto = new Producto();
$json = "";
$headers = apache_request_headers();
$operacion = "";
$nAfectados = -1;
if (isset($headers['tokenauth'])) {
    session_id($headers["tokenauth"]);
    session_start();
    if (isset($_SESSION['usuario']) && isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador') {
        if (isset($_REQUEST['id']) && isset($_REQUEST['operacion']) && !empty($_REQUEST['id']) && !empty($_REQUEST['operacion'])) {
            $operacion = $_REQUEST['operacion'];
            if (in_array($operacion, $operacionesAdmitidas)) {
                if (is_numeric($_REQUEST['id'])) {
                    $objProducto->setId($_REQUEST['id']);
                    if ($operacion == 'crear' || $operacion == 'actualizar') {
                        if (isset($_REQUEST['nombre']) && !empty($_REQUEST['nombre']) && isset($_REQUEST['descripcion']) && !empty($_REQUEST['descripcion']) && isset($_REQUEST['sabores']) && !empty($_REQUEST['sabores']) && isset($_REQUEST['tipo']) && !empty($_REQUEST['tipo']) && isset($_REQUEST['linea']) && !empty($_REQUEST['linea']) && isset($_REQUEST['precio']) && !empty($_REQUEST['precio'])) {
                            $objProducto->setNombre($_REQUEST['nombre']);
                            $objProducto->setDescripcion($_REQUEST['descripcion']);
                            //decodificacion del arreglo de sabores
                            if (is_array($_REQUEST['sabores'])) {
                                $objProducto->setSabores($_REQUEST['sabores']);
                            } else {
                                $nErr = Errores::ERROR_DATOS;
                            }
                            //manejo de tipo
                            $tipo = new Tipo();
                            if (is_numeric($_REQUEST['tipo'])) {
                                $tipo->setId($_REQUEST['tipo']);
                                $objProducto->setTipo($tipo);
                            }

                            //manejo de linea
                            $linea = new Linea();
                            if (is_numeric($_REQUEST['linea'])) {
                                $linea->setId($_REQUEST['linea']);
                                $objProducto->setLinea($linea);
                            }

                            //validacion del precio (que sea un numero)
                            if (is_numeric($_REQUEST['precio']))
                                $objProducto->setPrecio((float) $_REQUEST['precio']);
                            else
                                $nErr = Errores::ERROR_DATOS;
                            //manejo de la imagen

                            if ($operacion === "crear") {
                                if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
                                    if (in_array($_FILES['fotografia']['type'], $tiposPermitidos)) {
                                        $fotografiaTmp = $_FILES['fotografia']['tmp_name'];
                                        $fotografiaNombre = $_FILES['fotografia']['name'];
                                        $nuevoNombre = uniqid() . '_' . $fotografiaNombre;
                                        $nuevaRuta = __DIR__ . "/../images/" . $nuevoNombre;
                                        if (!move_uploaded_file($fotografiaTmp, $nuevaRuta)) {
                                            $nErr = Errores::ARCH_NO_COPIADO;
                                        } else {
                                            $objProducto->setFotografia($nuevoNombre);
                                        }
                                    } else {
                                        $nErr = Errores::ERROR_DATOS;
                                    }
                                } else {
                                    $nErr = Errores::ARCH_PROBL;
                                }
                            }
                        }
                    }
                    try {
                        if ($nErr == -1) {
                            switch ($operacion) {
                                case 'crear':
                                    $nAfectados = $objProducto->insertar();
                                    break;
                                case 'eliminar':
                                    $nAfectados = $objProducto->eliminar();
                                    break;
                                case 'actualizar':
                                    $nAfectados = $objProducto->modificar();
                                    break;
                            }
                            //Si no afectó al menos un registro, se trata de un error
                            if ($nAfectados < 1)
                                $nErr = Errores::OPE_NO_REALIZADA;
                        }
                    } catch (Exception $e) {
                        error_log($e->getFile() . " " . $e->getLine() . " " . $e->getMessage(), 0);
                        $nErr = Errores::ERROR_EN_BD;
                    }
                } else {
                    $nErr = Errores::ERROR_DATOS;
                }
            } else {
                $nErr = Errores::OP_NO_VALIDA;
            }
        } else {
            $nErr = Errores::FALTAN_DATOS;
        }
    } else {
        $nErr = Errores::SIN_PERMISOS;
    }
} else {
    $nErr = Errores::NO_FIRMADO;
}

if ($nErr == -1) {
    $json =
        '{
        "success":true,
        "status": "ok",
        "data":{}
    }';
} else {
    $oErr = new Errores();
    $oErr->setError($nErr);
    $json =
        '{
        "success":false,
        "status": "' . $oErr->getTextoError() . '",
        "data":{}
    }';
}
header('Content-type: application/json');
echo $json;