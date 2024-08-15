<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router){
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarLogin();
            if(empty($alertas)){
                //Verificar que el usuario exista
                $usuario = Usuario::where('email', $usuario->email);
                if(!$usuario || !$usuario->confirmado){
                    Usuario::setAlerta('error', 'El usuario no existe o no esta Confirmado');
                } else{
                    //El ususario existe
                    if(password_verify($_POST['password'], $usuario->password)){
                        //iniciar sesion
                        $resultado= session_start();
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        //Redireccionar 
                        if($resultado){
                            header('Location: /dashboard');
                        }
                        
                    }else{
                        Usuario::setAlerta('error', 'Password Incorrecto');
                    }
                }
                
            }
        }
        $alertas = Usuario::getAlertas();
        //Render a la vista
        $router->render('auth/login', [
            'titulo'=>'Iniciar Sesion',
            'alertas'=> $alertas
        ]);
    }
    public static function logout(){
        session_start();
        $_SESSION = [];
        header('Location: /');

    }
    public static function crear(Router $router){
        $alertas = [];
        $usuario = new Usuario;
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            
            if(empty($alertas)){
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario){
                    Usuario::setAlerta('error', 'El Usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                }else{
                    //Hash el password
                    $usuario->hashPassword();

                    //Eliminar password2
                    unset($usuario->password2);

                    //generar token
                    $usuario->crearToken();
                    
                    //Crear un Nuevo Usuario
                    $resultado = $usuario->guardar();
                    
                    //Enviar Email
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarConfirmacion();
                    if($resultado){
                        header('Location: /mensaje');
                    }
                }
            }
        }
        //Render a la vista
        $router->render('auth/crear', [
            'titulo'=>'Crear Usuario',
            'usuario'=> $usuario,
            'alertas'=> $alertas

        ]);
    }
    public static function olvide(Router $router){
        $alertas = [];
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)){
                //Buscar el Usuario
                $usuario = Usuario::where('email', $usuario->email);
                if($usuario && $usuario->confirmado){
                    //Encontro el usuario

                    //Generar un Nuevo Token
                    $usuario->crearToken();
                    unset($usuario->password2);
                    //Actualizar el password
                    $usuario->guardar();
                    //Enviar el Email 
                    $email = new Email($usuario->nombre, $usuario->email, $usuario->token);
                    $email->enviarInstrucciones();
                    
                    //Imprimir la Alerta
                    Usuario::setAlerta('exito', 'Hemos enviado Instrucciones a tu email');
                } else{
                    Usuario::setAlerta('error', 'El Usuario no existe o no esta confirmado');
                    
                }
            }
        }
        $alertas = Usuario::getAlertas();

        //Muestra la Vista
        $router->render('auth/olvide', [
            'titulo'=>'Olvide mi Password',
            'alertas'=> $alertas
        ]);
    }
    public static function reestablecer(Router $router){
        $token = s($_GET['token']);
        $mostrar = true;
        if(!$token) header('Location: /');

        //Encontrar al Usuario con este token
        $usuario = Usuario::where('token', $token);
        if(empty($usuario)){
            //No se encontro un usuario con ese token
            Usuario::setAlerta('error', 'Token No Valido');
            $mostrar = false;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            //Añadir el Nuevo Password
            $usuario->sincronizar($_POST);
            //Validar el Password
            $alertas = $usuario->validarPassword();
            
            if(empty($alertas)){
                //Hash el password
                $usuario->hashPassword();

                //Eliminar password2
                unset($usuario->password2);

                //Eliminar Token
                $usuario->token = null;
                // Guardar en la Base de Datos
                $resultado = $usuario->guardar();
                if($resultado){
                    header('Location: /');
                }
            }
        }
        $alertas = Usuario::getAlertas();
        $router->render('auth/reestablecer', [
            'titulo'=>'Reestablecer Password',
            'alertas'=> $alertas,
            'mostrar'=> $mostrar
        ]);
    }
    public static function mensaje(Router $router){
        $router->render('auth/mensaje',[
            'titulo'=>'Mensaje'
        ]);

    }
    public static function confirmar(Router $router){
        $token = s($_GET['token']);

        if(!$token) header('Location: /');
        //Encontrar al Usuario con este token
        $usuario = Usuario::where('token', $token);
        

        if(empty($usuario)){
            //No se encontro un usuario con ese token
            Usuario::setAlerta('error', 'Token No Valido');
        }else{
            //confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);
            
            //Guardar en la Base de datos
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta Confirmada, ahora puede Iniciar Sesion');
        }
        $alertas = Usuario::getAlertas();
        
        
        $router->render('auth/confirmar',[
            'titulo'=>'Confirmar Cuenta',
            'alertas'=> $alertas
        ]);

    }
}