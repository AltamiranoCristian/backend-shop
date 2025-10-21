<?php
class Sabor{
    private ?int $id = null;
    private string $nombre;

    public function insertar(): int {
        $oAccesoDatos = new AccesoDatos();
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "INSERT INTO sabor (nombre) VALUES (:nombre)";
                $arrParams = [":nombre" => $this->nombre];
                $nAfectados = $oAccesoDatos->ejecutarComando($sQuery, $arrParams);
                $oAccesoDatos->desconectar();
                return $nAfectados;
            }
        } catch (Exception $e) {
            error_log("Error en Sabor->insertar(): " . $e->getMessage());
        }
        return 0;
    }

    public static function buscarTodos(): array {
        $oAccesoDatos = new AccesoDatos();
        $sabores = [];
        $oSabores = null;
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT id, nombre FROM sabor";
                $result = $oAccesoDatos->ejecutarConsulta($sQuery, []);
                foreach ($result as $row) {
                    $oSabores = new Sabor();
                    $oSabores->setId($row[0] ?? 0);
                    $oSabores->setNombre($row[1]);
                    $sabores[] = $oSabores;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Sabor->buscarTodos(): " . $e->getMessage());
        }
        return $sabores;
    }

    // Getters y Setters
    public function getId(): ?int { return $this->id ?? null; }
    public function setId(int $valor){ $this->id = $valor; }
    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $valor){ $this->nombre = $valor; }

}