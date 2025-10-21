<?php
error_reporting(E_ALL);
include_once 'Persona.php';
include_once 'Direccion.php';
class Cliente extends Persona {
    private ?Direccion $direccion; 
    private ?string $tel_casa;
    private ?string $tel_cel;

    public function setDireccion(Direccion $valor) {
        $this->direcciones = $valor;
    }

    public function getDirecciones():? Direccion {
        return $this->direccion;
    }

    public function setTelefonoCasa(string $valor){
        $this->tel_casa = $valor;
    }
    public function getTelefonoCasa():?string{
        return $this->tel_casa;
    }

    public function setTelefonoCel(string $valor){
        $this->tel_cel = $valor;
    }
    public function getTelefonoCel():?string{
        return $this->tel_cel;
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
                $sQuery = "SELECT t1.nombre, t1.primerApellido, t1.segundoApellido
					FROM persona t1
						JOIN cliente t2 ON t2.id = t1.id
					WHERE t1.correo = :correo AND t1.contrasenia = :contrasenia AND t1.activa = TRUE";
                $arrParams = array(":correo"=>$this->correo, ":contrasenia"=>$this->contrasenia);
                $arrRS = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
                $oAccesoDatos->desconectar();
                if ($arrRS){
                    $this->nombre = $arrRS[0][0];
                    $this->primerApellido = $arrRS[0][1];
                    $this->segundoApellido = $arrRS[0][2];
                    $bRet = true;
                }
            }
        }return $bRet;
    }

    public function buscarTodos(): array {
        throw new Exception("Unsupported Operation");
    }

    public function insertar(): int {
        
        $oAccesoDatos = new AccesoDatos();
        $nAfectadosPersona = 0;
        $nAfectadosDireccion = 0;
        $clienteId = 0; // Inicializa la variable para almacenar el ID del cliente
    
        try {
            if ($oAccesoDatos->conectar()) {
                // Corregir los parámetros en la consulta
                $sQuery = "INSERT INTO persona (nombre, primerapellido, segundoapellido, correo, contrasenia, activa)
                            VALUES (:nombre, :primerapellido, :segundoapellido, :correo, :contrasenia, :activa) RETURNING id";
                $arregloParametros = array(
                    ":nombre" => $this->nombre,
                    ":primerapellido" => $this->primerApellido,
                    ":segundoapellido" => $this->segundoApellido,
                    ":correo" => $this->correo,
                    ":contrasenia" => $this->contrasenia,
                    ":activa" => $this->activa,
                );
    
                $arregloResultado = $oAccesoDatos->ejecutarConsulta($sQuery, $arregloParametros);
    
                if ($arregloResultado && count($arregloResultado) > 0) {
                    $clienteId = $oAccesoDatos->getLastInsertId();
                    $nAfectadosPersona = 1;
    
                    // Asegúrate de que estas propiedades están inicializadas correctamente
                    $sQuery = "INSERT INTO cliente (id, telefonocasa, telefonocel) 
                                VALUES (:id, :telefonocasa, :telefonocel)";
                    $arrParams = array(
                        ":id" => $clienteId,
                        ":telefonocasa" => $this->tel_casa,
                        ":telefonocel" => $this->tel_cel,
                    );
    
                    $nAfectadosCliente = $oAccesoDatos->ejecutarConsulta($sQuery, $arrParams);
    
                    // Inserta la dirección si existe
                    if (isset($this->direccion)) {
                        $nAfectadosDireccion = $this->direccion->insertar($clienteId);
                    }
    
                    // Verifica si se insertaron correctamente las direcciones y los datos del cliente
                    if ($nAfectadosCliente === false || $nAfectadosDireccion === false) {
                        throw new Exception("Error al insertar en las tablas cliente y/o direccion");
                    }
                } else {
                    throw new Exception("Error al insertar en la tabla persona");
                }
                $oAccesoDatos->desconectar();
            }
        } catch (Exception $th) {
            error_log("Error en insertar(): " . $th->getMessage());
            return 0; // Devuelve 0 en caso de error
        }
    
        // Asegúrate de devolver el ID del cliente en lugar del número de filas afectadas
        return $clienteId;
    }
    
    

    public function modificar(): int {
        throw new Exception("Unsupported Operation");
    }

    public function eliminar(): int {
        throw new Exception("Unsupported Operation");
    }
}
