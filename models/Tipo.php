<?php
class Tipo{
    private ?int $id;
    private string $nombre;

    public function insertar(): int {
        $oAccesoDatos = new AccesoDatos();
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "INSERT INTO tipo (nombre) VALUES (:nombre)";
                $arrParams = [":nombre" => $this->nombre];
                $nAfectados = $oAccesoDatos->ejecutarComando($sQuery, $arrParams);
                $oAccesoDatos->desconectar();
                return $nAfectados;
            }
        } catch (Exception $e) {
            error_log("Error en Tipo->insertar(): " . $e->getMessage());
        }
        return 0;
    }

    public static function buscarTodos(): array {
        $oAccesoDatos = new AccesoDatos();
        $tipos = [];
        $oTipo = null;
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT * FROM tipo";
                $result = $oAccesoDatos->ejecutarConsulta($sQuery, []);
                foreach ($result as $row) {
                    $oTipo = new Tipo();
                    $oTipo->setId($row[0]);
                    $oTipo->setNombre($row[1]);
                    $tipos [] = $oTipo;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Tipo->buscarTodos(): " . $e->getMessage());
        }
        return $tipos;
    }

    public static function buscarPorId(int $id): ?Tipo {
        $oAccesoDatos = new AccesoDatos();
        $tipo = null;
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT * FROM tipo WHERE id = :id";
                $arrParams = [":id" => $id];
                $result = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
                if (count($result) > 0) {
                    $tipo = new tipo();
                    $tipo->setId($result[0]['id']);
                    $tipo->setNombre($result[0]['nombre']);
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Tipo->buscarPorId(): " . $e->getMessage());
        }

        return $tipo;
    }

    // Getters y Setters
    public function getId(): ?int { return $this->id; }
    public function setId(int $valor){ $this->id = $valor; }
    public function getNombre(): string { return $this->nombre; }
    public function setNombre(string $valor){ $this->nombre = $valor; }
}