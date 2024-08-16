<?php

namespace Controllers;

use Classes\Email;
use MVC\Router;
use Model\Usuario;


class LoginController {
    

    public static function login(Router $router) {
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // $usuario = new Usuario($_POST);
            // echo 'Desde POST';
            $auth = new Usuario($_POST);

            $alertas = $auth->validarLogin();

            if (empty($alertas)) {
                // echo 'El usuario proporciono correo y contraseña';
                // comprobar que exista el usurio
                $usuario = Usuario::buscarPorCampo('email', $auth->email);

                if ($usuario) {
                    //comprobar la contraseña

                    if($usuario->comprobarContrasenaAndVerificado($auth->password)) {

                        //autenticar usuario
                        // TAREA++++++++++++
                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . ' ' . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // debuguear($_SESSION);

                        //Redireccionamiento

                        if ($usuario->admin == 1) {
                            $_SESSION['admin'] = $usuario->admin ?? null;
                            header('location: /admin');
                        } else {
                            header('location: /cliente');
                        }

                    }

                } else {
                    Usuario::setAlerta('error', 'Usuario no encontrado');
                }
            }

        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/login',[
            'alertas' => $alertas
        ]);
    }




    public static function logout() {
        echo 'Desde logout';
    }

    public static function olvide(Router $router) {
        
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarEmail();

            if(empty($alertas)) {
                $usuario = Usuario::buscarPorCampo('email', $auth->email);
                
                if($usuario && $usuario->confirmado == 1) {
                    $usuario->crearToken();
                    $usuario->guardar();

                    //TODO: enviar el email
                    $email = new Email(
                        $usuario->email,
                        $usuario->nombre,
                        $usuario->token
                    );
                    $email->enviarInstrucciones();

                    Usuario::setAlerta('exito','revisa tu email');
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe o no esta confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/olvide-password', [
            'alertas' => $alertas
        ]);
    }


    public static function crear(Router $router) {
        
        $usuario = new Usuario;
        //alertas vacia
        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();
            

            //Revisar que alerta este vacia
            if (empty($alertas)) {

                //Verifica que el usuario no este registrado
                $resultado = $usuario->existeUsuario();
                

                if($resultado->num_rows){
                    $alertas = Usuario::getAlertas();
                }else {
                    //Hashear el password
                    $usuario->hashPassword();

                    //Geberar un Token unico
                    $usuario->crearToken();
                    
                    //Enviar el imal
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    //Crear el usuario 
                    $resultado = $usuario->guardar();
                    
                    //debuguear($usuario);

                    if($resultado) {
                        header('Location: /mensaje');
                    }


                }

            }
        }
        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function confirmar(Router $router) {
        $alertas = [];

        $token = s ($_GET['token']);

        //debuguear($token);

        $usuario = Usuario::buscarPorCampo('token', $token);
        
        if(empty($usuario)) {
            // echo 'Token no valido';
            Usuario::setAlerta('error', 'Token no valido');
        }else {
            //Modificar a usuario confirmado
            // echo 'Token valido, confirmando usuario...';

            $usuario->confirmado = '1';
            $usuario->token = '';

            // debuguear($usuario);

            $usuario->guardar();
            Usuario::setAlerta('exito', 'cuenta comprobada correctamente');

        }

        //Obtener alertas
        $alertas = Usuario::getAlertas();

        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }

    public static function mensaje(Router $router) {
        
        $router->render('auth/mensaje');
    }

    public static function admin() {
        echo 'Desde admin';
    }

    public static function cliente() {
        echo 'Desde cliente';
    }



    


    public static function recuperar(Router $router) {

        $alertas = [];

        $error = false;

        $token = s($_GET['token']);

        // Buscar usuario por su token
        $usuario = Usuario::buscarPorCampo('token', $token);

        //debugear($usuario);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no valido');
            $error = true;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

            //Leer el nuevo password y guardarlo
            $password = new Usuario($_POST);
            $alertas = $password->validarPassword();

            if(empty($alertas)) {
                $usuario->password = null;

                $usuario->password = $password->password;
                $usuario->hashPassword();
                $usuario->token = null;

                $resultado = $usuario->guardar();
                if($resultado) {
                    header('Location: /');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        $router->render('auth/recuperar-password', [
            'alertas' => $alertas,
            'error' => $error
        ]);
    }
}