<?php
error_reporting(E_ALL);
abstract class Persona {

protected ?string $nombre='';
protected ?string $primerApellido='';
protected ?string $segundoApellido='';
protected ?string $correo='';
protected ?string $contrasenia='';
protected bool $activa=true;

	abstract public function buscar():bool;
	
	abstract public function buscarPorId():bool;

	abstract public function buscarTodos():array;

	abstract public function insertar():int;

	abstract public function modificar():int;

	abstract public function eliminar():int;
	
    public function setNombre(string $valor){
        $this->nombre = $valor;
    }
    public function getNombre():?string{
        return $this->nombre;
    }
    
    public function setPrimerApellido(string $valor){
        $this->primerApellido = $valor;
    }
    public function getPrimerApellido():?string{
        return $this->primerApellido;
    }

    public function setSegundoApellido(?string $valor){
        $this->segundoApellido = $valor;
    }
    public function getSegundoApellido():?string{
        return $this->segundoApellido;
	}

    public function setCorreo(string $valor){
        $this->correo = $valor;
    }
    public function getCorreo():?string{
        return $this->correo;
    }
	
    public function setContrasenia(string $valor){
        $this->contrasenia = $valor;
    }
    public function getContrasenia():?string{
        return $this->contrasenia;
    }

    public function setActiva(bool $valor){
        $this->activa = $valor;
    }
    public function getActiva():bool{
        return $this->activa;
	}
	
	public function getNombreCompleto():string{
		return $this->nombre." ".$this->primerApellido." ".$this->segundoApellido;
	}
}