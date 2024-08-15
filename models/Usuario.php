<?php

namespace Model;

class Usuario extends ActiveRecord{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public $id;
    public $nombre;
    public $email;
    public $password;
    public $password2;
    public $password_actual;
    public $password_nuevo;
    public $token;
    public $confirmado;
    public function __construct($args = [])
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->password_actual = $args['password_actual'] ?? '';
        $this->password_nuevo = $args['password_nuevo'] ?? '';
        $this->token = $args['token'] ?? null;
        $this->confirmado = $args['confirmado'] ?? 0;
    }
    //Validar Login de Uusuarios
    public function validarLogin(){
        if(!$this->email){
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]= 'Email no valido';
        }
        if(!$this->password){
            self::$alertas['error'][] = 'El password No puede ir Vacio';
        }
        return self::$alertas;
    }
    //Validacion para Cuentas Nuevas
    public function validarNuevaCuenta(){
        if(!$this->nombre){
            self::$alertas['error'][] = 'El Nombre del Usuario es Obligatorio';
        }
        if(!$this->email){
            self::$alertas['error'][] = 'El Email del Usuario es Obligatorio';
        }
        if(!$this->password){
            self::$alertas['error'][] = 'El password No puede ir Vacio';
        }
        if(strlen($this->password) < 6 ){
            self::$alertas['error'][] = 'El password debe Contener al menos 6 caracteres';
        }
        if($this->password !== $this->password2){
            self::$alertas['error'][] = 'Los passwords son Diferentes';
        }
        return self::$alertas;
    }
    //Valida un email
    public function validarEmail(){
        if(!$this->email){
            self::$alertas['error'][]= 'El Email es Obligatorio';
        }

        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)){
            self::$alertas['error'][]= 'Email no valido';
        }
        return self::$alertas;
    }
    //Valida el password
    public function validarPassword(){
        if(!$this->password){
            self::$alertas['error'][] = 'El password No puede ir Vacio';
        }
        if(strlen($this->password) < 6 ){
            self::$alertas['error'][] = 'El password debe Contener al menos 6 caracteres';
        }
        return self::$alertas;
    }
    //Validar perfil
    public function validarPerfil(){
        if(!$this->nombre){
            self::$alertas['error'][] = 'El nombre no puede ir Vacio';
        }
        if(!$this->email){
            self::$alertas['error'][] = 'El email no puede ir Vacio';
        }
        return self::$alertas;
    }
    // Configurar el nuevo password
    public function nuevoPassword() : array{
        if(!$this->password_actual){
            self::$alertas['error'][] = 'El Password Actual no puede ir vacio';
        }
        if(!$this->password_nuevo){
            self::$alertas['error'][] = 'El Password Nuevo no puede ir vacio';
        }
        if(strlen($this->password_nuevo) < 6 ){
            self::$alertas['error'][] = 'El Password debe contener al menos 6 caracteres';
        }
        return self::$alertas;
    }
    //Comprobar el Password
    public function comprobarPassword() : bool{
        return password_verify($this->password_actual, $this->password);
    }
    //hash el password
    public function hashPassword() : void{
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }
    //Generar Token
    public function crearToken() : void{
        $this->token = uniqid();
    }
}