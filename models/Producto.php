<?php
include_once '../config/conexion.php';
include_once 'Tipo.php';
include_once 'Linea.php';
include_once 'Sabor.php';
class Producto
{
    private ?int $id;
    private ?string $nombre;
    private ?Linea $linea;
    private ?Tipo $tipo;
    private ?string $descripcion;
    private ?string $fotografia;
    private float $precio;
    private ?array $sabores = [];

    public function insertar(): int
    {
        $oAccesoDatos = new AccesoDatos();
        $lineaId = $this->linea->getId();
        $tipoId = $this->tipo->getId();

        try {
            if ($oAccesoDatos->conectar()) {
                // Inserta el producto
                $sQuery = "INSERT INTO producto (nombre, linea_id, tipo_id, descripcion, fotografia, precio)
                            VALUES (:nombre, :linea_id, :tipo_id, :descripcion, :fotografia, :precio)";

                $arrParams = array(
                    ":nombre" => $this->nombre,
                    ":linea_id" => $lineaId,
                    ":tipo_id" => $tipoId,
                    ":descripcion" => $this->descripcion,
                    ":fotografia" => $this->fotografia,
                    ":precio" => $this->precio
                );
                $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);

                // Obtén el ID del producto recién insertado
                $nLastInsertId = $oAccesoDatos->getLastInsertId();
                $this->setId((int) $nLastInsertId);

                // Asociar sabores si están disponibles
                if (!empty($this->sabores) && is_array($this->sabores)) {
                    // Aquí asumimos que $this->sabores es un array de IDs
                    $saborIds = array_map(function ($saborId) {
                        return (int) $saborId; // nos aseguramos de que se trata de un entero
                    }, $this->sabores);
                    $this->asociarSabores($saborIds);
                }

                $oAccesoDatos->desconectar();
                return (int) $nLastInsertId;
            }
        } catch (Exception $e) {
            error_log("Error en Producto::insertar(): " . $e->getMessage());
        }
        return -1;
    }



    public function modificar(): int
    {
        $oAccesoDatos = new AccesoDatos();
        $lineaId = $this->linea ? $this->linea->getId() : null;
        $tipoId = $this->tipo ? $this->tipo->getId() : null;

        if ($this->id == 0) {
            throw new Exception("Producto->modificar: faltan datos");
        }

        $arrParams = [":id" => $this->id];
        $query = "UPDATE producto SET";
        $fieldsToUpdate = [];

        // Agregar condiciones y parámetros según los valores definidos
        if (isset($this->nombre) && !empty($this->nombre)) {
            $fieldsToUpdate[] = "nombre = :nom";
            $arrParams[":nom"] = $this->nombre;
        }

        if (isset($this->descripcion) && !empty($this->descripcion)) {
            $fieldsToUpdate[] = "descripcion = :desc";
            $arrParams[":desc"] = $this->descripcion;
        }

        if (isset($this->fotografia) && !empty($this->fotografia)) {
            $fieldsToUpdate[] = "fotografia = :foto";
            $arrParams[":foto"] = $this->fotografia;
        }

        if ($lineaId !== null) {
            $fieldsToUpdate[] = "linea_id = :linea_id";
            $arrParams[":linea_id"] = $lineaId;
        }

        if ($tipoId !== null) {
            $fieldsToUpdate[] = "tipo_id = :tipo_id";
            $arrParams[":tipo_id"] = $tipoId;
        }

        $fieldsToUpdate[] = "precio = :precio";
        $arrParams[":precio"] = $this->precio;

        $query .= " " . implode(", ", $fieldsToUpdate) . " WHERE id = :id";

        try {
            if ($oAccesoDatos->conectar()) {
                $oAccesoDatos->iniciarTransaccion();
                $oAccesoDatos->ejecutarComando($query, $arrParams);

                // Actualizar sabores si existen
                if (!empty($this->sabores) && is_array($this->sabores)) {
                    // Eliminar sabores antiguos
                    $oAccesoDatos->ejecutarComando("DELETE FROM producto_sabor WHERE producto_id = :producto_id", [":producto_id" => $this->id]);

                    // Insertar nuevos sabores
                    foreach ($this->sabores as $saborId) {
                        $sQuery = "INSERT INTO producto_sabor (producto_id, sabor_id) VALUES (:producto_id, :sabor_id)";
                        $oAccesoDatos->ejecutarComando($sQuery, [":producto_id" => $this->id, ":sabor_id" => $saborId]);
                    }
                }

                $oAccesoDatos->commitTransaccion();
                return 1;
            }
        } catch (Exception $e) {
            $oAccesoDatos->rollbackTransaccion();
            error_log("Error en Producto->modificar(): " . $e->getMessage());
        } finally {
            $oAccesoDatos->desconectar();
        }
        return -1;
    }


    public function eliminar(): int
    {
        $oAccesoDatos = new AccesoDatos();
        $nAfectados = 0;
        if ($this->id == 0)
            throw new Exception("Producto->eliminar: faltan datos");
        else {
            try {
                if ($oAccesoDatos->conectar()) {
                    $oAccesoDatos->iniciarTransaccion();
                    //eliminacion de la asociacion
                    $sQuery = "DELETE FROM producto_sabor WHERE producto_id = :producto_id";
                    $arrParams = [":producto_id" => $this->id];
                    $oAccesoDatos->ejecutarComando($sQuery, $arrParams);

                    //eliminacion del producto
                    $sQuery = "DELETE FROM producto WHERE id = :id";
                    $arrParams = [":id" => $this->id];
                    $nAfectados = $oAccesoDatos->ejecutarComando($sQuery, $arrParams);

                    $oAccesoDatos->commitTransaccion();
                }
            } catch (Exception $e) {
                $oAccesoDatos->rollbackTransaccion();
                error_log("Error en Producto->eliminar(): " . $e->getMessage());
            } finally {
                $oAccesoDatos->desconectar();
            }
        }
        return $nAfectados;
    }

    public function asociarSabores(array $saborIds): int
    {
        $oAccesoDatos = new AccesoDatos();
        $nAfectados = 0;
        try {
            if ($oAccesoDatos->conectar()) {
                foreach ($saborIds as $saborId) {
                    $sQuery = "INSERT INTO producto_sabor (producto_id, sabor_id) VALUES (:producto_id, :sabor_id)";
                    $arrParams = [
                        ":producto_id" => $this->id,
                        ":sabor_id" => $saborId
                    ];
                    $nAfectados += $oAccesoDatos->ejecutarComando($sQuery, $arrParams);
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Producto->asociarSabores(): " . $e->getMessage());
        }
        return $nAfectados;
    }

    public function obtenerSabores(int $clave): array
    {
        $oAccesoDatos = new AccesoDatos();
        $sabores = [];
        $sabor = null;
        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT s.id ,s.nombre 
                            FROM sabor s JOIN producto_sabor ps ON s.id = ps.sabor_id 
                            WHERE ps.producto_id = :producto_id";
                $arrParams = [":producto_id" => $clave];
                $result = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);

                foreach ($result as $row) {
                    $sabor = new Sabor();
                    $sabor->setId($row['id']);
                    $sabor->setNombre($row['nombre']);
                    $sabores[] = $sabor;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en Producto->obtenerSabores(): " . $e->getMessage());
        }
        return $sabores;
    }

    public function buscar(): bool
    {
        $oAccesoDatos = new AccesoDatos();
        $oProducto = null;
        $return = false;
        if ($this->id < 1)
            throw new Exception("Producto->buscar: faltan datos");
        else {
            if ($oAccesoDatos->conectar()) {
                $query = "SELECT p.id, p.nombre, p.descripcion, p.fotografia, p.precio, l.id, l.nombre, t.id, t.nombre FROM producto p JOIN linea l ON p.linea_id = l.id JOIN tipo t ON p.tipo_id = t.id WHERE p.id = :id";
                $arrParams = array(":id" => $this->id);
                $arrRS = $oAccesoDatos->ejecutarConsulta($query, $arrParams);
                $oAccesoDatos->desconectar();
                if ($arrRS) {
                    $this->setId($arrRS[0][0]);
                    $this->setNombre($arrRS[0][1]);
                    $this->setDescripcion($arrRS[0][2]);
                    $this->setFotografia($arrRS[0][3]);
                    $this->setPrecio($arrRS[0][4]);

                    $oLinea = new Linea();
                    $oLinea->setId($arrRS[0][5]);
                    $oLinea->setNombre($arrRS[0][6]);
                    $this->setLinea($oLinea);

                    $oTipo = new Tipo();
                    $oTipo->setId($arrRS[0][7]);
                    $oTipo->setNombre($arrRS[0][8]);
                    $this->setTipo($oTipo);

                    $sabores = $this->obtenerSabores($this->getId());
                    $this->setSabores($sabores);
                    $return = true;
                }
            }
        }
        return $return;
    }

    public function buscarTodos(): array
    {
        $oAccesoDatos = new AccesoDatos();
        $oProducto = null;
        $arregloProductos = array();

        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT p.id, p.nombre, p.descripcion, p.fotografia, p.precio, l.id, l.nombre, t.id, t.nombre
                            FROM producto p JOIN linea l ON p.linea_id = l.id JOIN tipo t ON p.tipo_id = t.id ORDER BY p.id";
                $arrRS = $oAccesoDatos->ejecutarConsulta($sQuery, []);
                foreach ($arrRS as $row) {
                    $oProducto = new Producto();
                    $oProducto->setId($row[0]);
                    $oProducto->setNombre($row[1]);
                    $oProducto->setDescripcion($row[2]);
                    $oProducto->setFotografia($row[3]);
                    $oProducto->setPrecio($row[4]);

                    $oLinea = new Linea();
                    $oLinea->setId($row[5]);
                    $oLinea->setNombre($row[6]);
                    $oProducto->setLinea($oLinea);

                    $oTipo = new Tipo();
                    $oTipo->setId($row[7]);
                    $oTipo->setNombre($row[8]);
                    $oProducto->setTipo($oTipo);

                    $sabores = $oProducto->obtenerSabores($oProducto->getId());
                    $oProducto->setSabores($sabores);
                    $arregloProductos[] = $oProducto;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en obtenerTodosLosProductos(): " . $e->getMessage());
        }
        return $arregloProductos;
    }

    public function buscarPorLinea(int $clave): array
    {
        $oAccesoDatos = new AccesoDatos();
        $oProducto = null;
        $arregloProductos = array();

        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT p.id, p.nombre, p.descripcion, p.fotografia, p.precio, l.id, l.nombre, t.id, t.nombre
                            FROM producto p JOIN linea l ON p.linea_id = l.id JOIN tipo t ON p.tipo_id = t.id
                            WHERE p.linea_id = :linea_id";
                $arrParams = [":linea_id" => $clave];
                $arrRS = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
                foreach ($arrRS as $row) {
                    $oProducto = new Producto();
                    $oProducto->setId($row[0]);
                    $oProducto->setNombre($row[1]);
                    $oProducto->setDescripcion($row[2]);
                    $oProducto->setFotografia($row[3]);
                    $oProducto->setPrecio($row[4]);

                    $oLinea = new Linea();
                    $oLinea->setId($row[5]);
                    $oLinea->setNombre($row[6]);
                    $oProducto->setLinea($oLinea);

                    $oTipo = new Tipo();
                    $oTipo->setId($row[7]);
                    $oTipo->setNombre($row[8]);
                    $oProducto->setTipo($oTipo);

                    $sabores = $oProducto->obtenerSabores($oProducto->getId());
                    $oProducto->setSabores($sabores);
                    $arregloProductos[] = $oProducto;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en obtenerTodosLosProductos(): " . $e->getMessage());
        }
        return $arregloProductos;
    }

    public function buscarPorTipo(int $clave): array
    {
        $oAccesoDatos = new AccesoDatos();
        $oProducto = null;
        $arregloProductos = array();

        try {
            if ($oAccesoDatos->conectar()) {
                $sQuery = "SELECT p.id, p.nombre, p.descripcion, p.fotografia, p.precio, l.id, l.nombre, t.id, t.nombre
                            FROM producto p JOIN linea l ON p.linea_id = l.id JOIN tipo t ON p.tipo_id = t.id
                            WHERE p.tipo_id = :tipo";
                $arrParams = [":tipo" => $clave];
                $arrRS = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
                foreach ($arrRS as $row) {
                    $oProducto = new Producto();
                    $oProducto->setId($row[0]);
                    $oProducto->setNombre($row[1]);
                    $oProducto->setDescripcion($row[2]);
                    $oProducto->setFotografia($row[3]);
                    $oProducto->setPrecio($row[4]);

                    $oLinea = new Linea();
                    $oLinea->setId($row[5]);
                    $oLinea->setNombre($row[6]);
                    $oProducto->setLinea($oLinea);

                    $oTipo = new Tipo();
                    $oTipo->setId($row[7]);
                    $oTipo->setNombre($row[8]);
                    $oProducto->setTipo($oTipo);

                    $sabores = $oProducto->obtenerSabores($oProducto->getId());
                    $oProducto->setSabores($sabores);
                    $arregloProductos[] = $oProducto;
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $e) {
            error_log("Error en obtenerTodosLosProductos(): " . $e->getMessage());
        }
        return $arregloProductos;
    }

    // Getters y Setters
    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $valor)
    {
        $this->id = $valor;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function setNombre(string $valor)
    {
        $this->nombre = $valor;
    }
    public function getLinea(): Linea
    {
        return $this->linea;
    }
    public function setLinea(Linea $valor)
    {
        $this->linea = $valor;
    }

    public function getTipo(): Tipo
    {
        return $this->tipo;
    }
    public function setTipo(Tipo $valor)
    {
        $this->tipo = $valor;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }
    public function setDescripcion(string $valor)
    {
        $this->descripcion = $valor;
    }
    public function getFotografia(): ?string
    {
        return $this->fotografia;
    }
    public function setFotografia(string $valor)
    {
        $this->fotografia = $valor;
    }
    public function getPrecio(): float
    {
        return $this->precio;
    }
    public function setPrecio(float $valor)
    {
        $this->precio = $valor;
    }
    public function getSabores(): array
    {
        return $this->sabores;
    }
    public function setSabores(array $sabores): void
    {
        $this->sabores = $sabores;
    }
}
