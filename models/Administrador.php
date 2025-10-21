<?php
include_once 'Persona.php';
class Administrador extends Persona{
    private string $telefono;

    public function setTelefono(string $valor){
        $this->telefono = $valor;
    }

    public function getTelefono(): string{
        return $this->telefono;
    }

    public function insertar(): int {
        throw new Exception("Unsupported Operation");
    }
    public function modificar(): int {
        throw new Exception("Unsupported Operation");
    }

    public function eliminar(): int {
        throw new Exception("Unsupported Operation");
    }

    public function buscar(): bool {
        throw new Exception("Unsupported Operation");
    }

    public function buscarPorId(): bool {
        $oAccesoDatos = new AccesoDatos();
        $sQuery = "";
        $arrRS = null;
        $bRet = false;
        $arrParams = array();
        if (empty($this->correo) || empty($this->contrasenia))
            throw new Exception("Administrador->buscarCvePwd: faltan datos");
        else{
            if ($oAccesoDatos->conectar()){
                $sQuery = "SELECT t1.nombre, t1.primerApellido, t1.segundoApellido, t2.telefono
					FROM persona t1
						JOIN administrador t2 ON t2.id = t1.id
					WHERE t1.correo = :correo AND t1.contrasenia = :contrasenia AND t1.activa = TRUE";
                $arrParams = array(":correo"=>$this->correo, ":contrasenia"=>$this->contrasenia);
                $arrRS = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
                $oAccesoDatos->desconectar();
                if ($arrRS){
                    $this->nombre = $arrRS[0][0];
                    $this->primerApellido = $arrRS[0][1];
                    $this->segundoApellido = $arrRS[0][2];
                    $this->telefono = $arrRS[0][3];
                    $bRet = true;
                }
            }
        }return $bRet;
    }

    public function buscarTodos(): array {
        throw new Exception("Unsupported Operation");
    }
}