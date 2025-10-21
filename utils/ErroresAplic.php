<?php
/*
Archivo:  ErroresAplic.php
Objetivo: clase que encapsula los errores que manejan la mayoría de 
		las aplicaciones a utilizar en el semestre.
Autor:    BAOZ
*/
class Errores
{
	private int $nError = -1;
	//Errores considerados
	const NO_FIRMADO = 1;
	const USR_DESCONOCIDO = 2;
	const ERROR_EN_BD = 3;
	const FALTAN_DATOS = 4;
	const NO_EXISTE_BUSCADO = 5;
	const SIN_PERMISOS = 6;
	const ERROR_DATOS = 7;
	const ERROR_NAV = 8;
	const ARCH_NO_COPIADO = 9;
	const ARCH_MAYOR = 10;
	const ARCH_TIPO_MAL = 11;
	const ARCH_PROBL = 12;
	const OP_NO_VALIDA = 13;
	const OPE_NO_REALIZADA = 14;
	public function getError()
	{
		return $this->nError;
	}
	public function setError(int $val)
	{
		$this->nError = $val;
	}

	public function getTextoError()
	{
		$sMsjError = "";
		switch ($this->nError) {
			case self::NO_FIRMADO:
				$sMsjError = "No ha ingresado al sistema";
				break;
			case self::USR_DESCONOCIDO:
				$sMsjError = "Usuario desconocido";
				break;
			case self::ERROR_EN_BD:
				$sMsjError = "Error al acceder al repositorio";
				break;
			case self::FALTAN_DATOS:
				$sMsjError = "Faltan datos";
				break;
			case self::NO_EXISTE_BUSCADO:
				$sMsjError = "No existe el registro buscado";
				break;
			case self::SIN_PERMISOS:
				$sMsjError = "No tiene permisos para ver la pantalla solicitada";
				break;
			case self::ERROR_DATOS:
				$sMsjError = "Los datos son de tipo erróneo";
				break;
			case self::ERROR_NAV:
				$sMsjError = "Error de navegación";
				break;
			case self::ARCH_MAYOR:
				$sMsjError = "El archivo es mayor a 200 KB";
				break;
			case self::ARCH_TIPO_MAL:
				$sMsjError = "El archivo es de tipo incorrecto";
				break;
			case self::ARCH_PROBL:
				$sMsjError = "El archivo presenta problemas";
				break;
			case self::OP_NO_VALIDA:
				$sMsjError = "La Operacion a realizar no es Valida";
				break;
			case self::OPE_NO_REALIZADA:
				$sMsjError = "La Operacion no se pudo realizar";
				break;
			case self::ARCH_NO_COPIADO:
				$sMsjError = "No se pudo copiar el archivo";
				break;
			default:
				$sMsjError = "Error desconocido";
		}
		return $sMsjError;
	}
}
?>