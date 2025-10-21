<?php
error_reporting(E_ALL);
include_once '../config/conexion.php';
class Direccion {
    private string $calle;
    private string $ciudad;
    private string $estado;
    private string $codigoPostal;
    private string $pais;

    public function __construct(string $calle, string $ciudad, string $estado, string $codigoPostal, string $pais) {
        $this->calle = $calle;
        $this->ciudad = $ciudad;
        $this->estado = $estado;
        $this->codigoPostal = $codigoPostal;
        $this->pais = $pais;
    }

    public function getCalle(): string {
        return $this->calle;
    }

    public function setCalle(string $valor){
        $this->calle = $valor;
    }

    public function getCiudad(): string {
        return $this->ciudad;
    }

    public function setCiudad(string $valor){
        $this->ciudad = $valor;
    }

    public function getEstado(): string {
        return $this->estado;
    }

    public function setEstado(string $valor){
        $this->estado = $valor;
    }

    public function getCodigoPostal(): string {
        return $this->codigoPostal;
    }

    public function setCodigoPostal(string $valor){
        $this->codigoPostal = $valor;
    }

    public function getPais(): string {
        return $this->pais;
    }

    public function setPais(string $valor){
        $this->pais = $valor;
    }

    public function insertar(int $idcliente): int{
        $oAccesoDatos = new AccesoDatos();
        $nAfectados = 0;

        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "INSERT INTO direccion (calle, ciudad, estado, codigoPostal, pais, cliente_id) 
                            VALUES (:calle, :ciudad, :estado, :codigoPostal, :pais, :cliente_id)";
                $arrParams = array(
                    ":calle" => $this->calle,
                    ":ciudad" => $this->ciudad,
                    ":estado" => $this->estado,
                    ":codigoPostal" => $this->codigoPostal,
                    ":pais" => $this->pais,
                    ":cliente_id" => $idcliente
                );
                $nAfectados = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $th) {
            error_log("Error en Direccion::insertar(): " . $th->getMessage());
        }
        return count($nAfectados);
    }
}
