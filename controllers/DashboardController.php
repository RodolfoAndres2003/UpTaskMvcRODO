<?php 

namespace Controllers;

use Model\Proyecto;
use Model\Usuario;
use MVC\Router;


class DashboardController{
    public static function index(Router $router){
        session_start();
        isAuth();
        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }
    public static function crear_proyecto(Router $router){
        session_start();
        isAuth();
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $proyecto = new Proyecto($_POST);
            //validacion
            $alertas = $proyecto->validarProyecto();
            if(empty($alertas)){
                //Generar una url Unica
                $hash = md5(uniqid());
                $proyecto->url = $hash;
                //Almacenar el Creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];
                
                //Guardar el Proyecto
                $proyecto->guardar();
                
                
                header('Location: /proyecto?id=' . $proyecto->url);
               

            }
        }
        $router->render('dashboard/crear-proyecto',[
            'alertas' => $alertas,
            'titulo'=> 'Crear Proyecto'
        ]);
    }


    public static function proyecto(Router $router){
        session_start();
        isAuth();
        $token =$_GET['id'];
        if(!$token) header('Location: /dashboard');

        //Autenticar el usuario para mostrar los proyectos
        $proyecto = Proyecto::where('url' , $token);
        if($proyecto->propietarioId !== $_SESSION['id']){
            header('Location: /dashboard');
        }
        $router->render('dashboard/proyecto',[
            'titulo' => $proyecto->proyecto
            
        ]);
    }


    public static function perfil(Router $router){
        session_start();
        isAuth();
        $alertas = [];
        $usuario = Usuario::find($_SESSION['id']);
        
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarPerfil();

            if(empty($alertas)){
                $existeUsuario = Usuario::where('email', $usuario->email);
                if($existeUsuario && $existeUsuario->id !== $usuario->id){
                    //mostrar un mensaje de error
                    Usuario::setAlerta('error', 'este email ya existe');
                } else{
                    // GUARDAR EL REGISTRO
                    //Guardar el Usuario
                $usuario->guardar();
                    Usuario::setAlerta('exito', 'Guardado Correctamente');
                
                    // asignar nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                }
                $alertas = $usuario->getAlertas();
            }
        }

        // Enviar a la vista
        $router->render('dashboard/perfil',[
            'titulo' => 'Perfil',
            'alertas' => $alertas,
            'usuario' => $usuario
        ]);
    }
    public static function cambiar_password(Router $router){
        session_start();
        isAuth();
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $usuario = Usuario::find($_SESSION['id']);

            //sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);
            $alertas = $usuario->nuevoPassword();
            
            if(empty($alertas)){
                $resultado = $usuario->comprobarPassword();
                if($resultado){
                    // eliminar propiedades innecesarias
                    unset($usuario->password_actual);
                    unset($usuario->password_nuevo);
                    //asignar el nuevo password
                    $usuario->password = $usuario->password_nuevo;
                    //hashear el nuevo password
                    $usuario->hashPassword();
                    //Actualizar en la Bd
                    $resultado = $usuario->guardar();
                    if($resultado){
                        Usuario::setAlerta('exito', 'Password Actualizado Correctamente');
                    }
                    
                }else{
                    Usuario::setAlerta('error', 'Password incorrecto');
                }
            }
            $alertas = $usuario->getAlertas();
        }
        $router->render('dashboard/cambiar-password',[
            'titulo' => 'Cambiar Password',
            'alertas' => $alertas,
            
        ]);
    }
}