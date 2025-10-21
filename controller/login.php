<?php
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../models/Administrador.php';
require_once '../models/Cliente.php';
require_once '../utils/ErroresAplic.php';
session_start(); //Le avisa al servidor que va a utilizar sesiones
$nErr=-1;
$oUsu=new Administrador();
$sJsonRet = "";
$oErr = null;
	/*Verifica que hayan llegado los datos*/
	$data = json_decode(file_get_contents("php://input"), true);//decodificamos la respuesta en JSON
	if (isset($data["email"]) && !empty($data["email"]) && isset($data["password"]) && !empty($data["password"])){
		try{
			//Pasa los datos al objeto
			$oUsu->setCorreo($data["email"]);
			$oUsu->setContrasenia($data["password"]);
			//Busca en la base de datos
			if ($oUsu->buscarPorId()){
				//Si lo encuentra, genera la variablede sesión y guarda sus datos
				$_SESSION["usuario"] = $oUsu;
				$_SESSION["rol"] = "administrador";
				/*La variable de sesión va a servir para las páginas de PHP con navegación tradicional, mientras que el 
                token va a servir para los controladores que 
				devuelven JSON si necesitan verificar que pasó por el login */
			}else {
				//Si no es administrador, es posible que sea Cliente.
				$oUsu = new Cliente();
				$oUsu->setCorreo($data["email"]);
				$oUsu->setContrasenia($data["password"]);
				//Busca en la base de datos
				if ($oUsu->buscarPorId()){
					//Si lo encuentra, genera la variablede sesión y guarda sus datos
					$_SESSION["usuario"] = $oUsu;
					$_SESSION["rol"] = "cliente";
				}else
					$nErr = Errores::USR_DESCONOCIDO;
			}
		}catch(Exception $e){
			//Enviar el error específico a la bitácora de php (dentro de php\logs\php_error_log
			error_log($e->getFile()." ".$e->getLine()." ".$e->getMessage(),0);
			$nErr = Errores::ERROR_EN_BD;
		}
	}
	else
		$nErr = Errores::FALTAN_DATOS;
	
	if ($nErr==-1){
		$sJsonRet = 
		'{
			"success":true,
			"status": "ok",
			"data":{
				"nombre":"'.$oUsu->getNombreCompleto().'",
				"tipo": "'.(is_a($oUsu, 'Administrador')?'Administrador':'Cliente').'",
				"token":"'.session_id().'"
			}
		}';
	}else{
		$oErr = new Errores();
		$oErr->setError($nErr);
		$sJsonRet = 
		'{
			"success":false,
			"status": "'.$oErr->getTextoError().'",
			"data":{}
		}';
	}
	//Retornar JSON a quien hizo la llamada
	header('Content-type: application/json');
	echo $sJsonRet;