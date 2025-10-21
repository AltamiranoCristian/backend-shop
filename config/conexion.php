<?php 
error_reporting(E_ALL);
class AccesoDatos{
    private $oConexion = null;

    /* Realiza la conexión a la base de datos */
    function conectar(){
        $bRet = false;
        try {
            $this->oConexion = new PDO("pgsql:dbname=pasteleria2024; host=localhost; user=equipoldmc; password=P@ssword");
            // Configura la conexión para que lance excepción en caso de errores
            $this->oConexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $bRet = true;
        } catch (Exception $e) {
            throw $e;
        }
        return $bRet;
    }

    /* Realiza la desconexión de la base de datos */
    function desconectar(){
        $bRet = true;
        if ($this->oConexion != null) {
            $this->oConexion = null;
        }
        return $bRet;
    }

    /* Ejecuta una consulta y devuelve los resultados como un array */
    function ejecutarConsulta($psConsulta, $parrParams){
        $arrRS = null;
        $rst = null;
        if ($psConsulta == "") {
            throw new Exception("AccesoDatos->ejecutarConsulta: falta indicar la consulta");
        }
        if ($this->oConexion == null) {
            throw new Exception("AccesoDatos->ejecutarConsulta: falta conectar la base");
        }
        try {
            $rst = $this->oConexion->prepare($psConsulta);
            $rst->execute($parrParams);
        } catch (Exception $e) {
            throw $e;
        }
        if ($rst) {
            $arrRS = $rst->fetchAll();
        }
        return $arrRS;
    }

    /* Ejecuta un comando (INSERT, UPDATE, DELETE) y devuelve el número de registros afectados */
    function ejecutarComando(string $psComando, array $parrParams){
        $nAfectados = -1;
        if ($psComando == "") {
            throw new Exception("AccesoDatos->ejecutarComando: falta indicar el comando");
        }
        if ($this->oConexion == null) {
            throw new Exception("AccesoDatos->ejecutarComando: falta conectar la base");
        }
        try {
            $pdo = $this->oConexion->prepare($psComando);
            $pdo->execute($parrParams);
            $nAfectados = $pdo->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
        return $nAfectados;
    }

    /* Iniciar transacción */
    function iniciarTransaccion() {
        if ($this->oConexion == null) {
            throw new Exception("AccesoDatos->iniciarTransaccion: falta conectar la base");
        }
        try {
            $this->oConexion->beginTransaction();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* Confirmar transacción */
    function commitTransaccion() {
        if ($this->oConexion == null) {
            throw new Exception("AccesoDatos->commitTransaccion: falta conectar la base");
        }
        try {
            $this->oConexion->commit();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* Revertir transacción */
    function rollbackTransaccion() {
        if ($this->oConexion == null) {
            throw new Exception("AccesoDatos->rollbackTransaccion: falta conectar la base");
        }
        try {
            $this->oConexion->rollBack();
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getLastInsertId(): ?int {
        return $this->oConexion ? $this->oConexion->lastInsertId() : null;
    }
}
