<?php
include_once '../config/conexion.php';
class Linea{
    private ?int $id;
    private string $nombre;

    public function insertar(): int {
        $oAccesoDatos = new AccesoDatos();
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "INSERT INTO linea (nombre) VALUES (:nombre)";
                $arrParams = [":nombre" => $this->nombre];
                $nAfectados = $oAccesoDatos->ejecutarComando($sQuery, $arrParams);
                $oAccesoDatos->desconectar();
                return $nAfectados;
            }
        } catch (Exception $e) {
            error_log("Error en Linea->insertar(): " . $e->getMessage());
        }
        return 0;
    }

    public static function buscarTodos(): array {
        $oAccesoDatos = new AccesoDatos();
        $lineas = [];
        $oLinea = null;
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT * FROM linea";
                $result = $oAccesoDatos->ejecutarConsulta($sQuery, []);
                foreach ($result as $row) {
                    $oLinea = new Linea();
                    $oLinea->setId($row[0]);
                    $oLinea->setNombre($row[1]);
                    $lineas [] = $oLinea;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Linea->buscarTodos(): " . $e->getMessage());
        }
        return $lineas;
    }

    public static function buscarPorId(int $id): ?Linea {
        $oAccesoDatos = new AccesoDatos();
        $linea = null;

        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT * FROM linea WHERE id = :id";
                $arrParams = [":id" => $id];
                $result = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);

                if (count($result) > 0) {
                    $linea = new Linea();
                    $linea->setId($result[0]['id']);
                    $linea->setNombre($result[0]['nombre']);
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Linea->buscarPorId(): " . $e->getMessage());
        }

        return $linea;
    }

    // Getters y Setters
    public function getId(): ?int { return $this->id; }
    public function setId(int $valor){ $this->id = $valor; }
    public function getNombre(): string { return $this->nombre; }
    public function setNombre(string $valor){ $this->nombre = $valor; }
}