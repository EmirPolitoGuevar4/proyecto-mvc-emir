<?php 

require_once __DIR__ . '/../includes/app.php';

use Controllers\LoginController;
use MVC\Router;
use PgSql\Lob;

$router = new Router();

//iniciar sesion 
$router->get('/',[ LoginController::class, 'login']); 
$router->post('/',[ LoginController::class, 'login']); 
$router->post('/logout',[ LoginController::class, 'logout']); 

//recuperar contraseña
$router->get('/olvide',[ LoginController::class, 'olvide']); 
$router->post('/olvide',[ LoginController::class, 'olvide']); 
$router->get('/recuperar',[ LoginController::class, 'recuperar']); 
$router->post('/recuperar',[ LoginController::class, 'recuperar']);

//crear cuenta
$router->get('/crear',[ LoginController::class, 'crear']); 
$router->post('/crear',[ LoginController::class, 'crear']);

//ruta> crear-cuenta---------------------------
$router->get('/crear-cuenta',[ LoginController::class, 'crear']); 
$router->post('/crear-cuenta',[ LoginController::class, 'crear']);

// //funcion> crear
// $router->get('/funcion_crear',[ LoginController::class, 'funcion_crear']); 
// $router->post('/funcion_crear',[ LoginController::class, 'funcion_crear']);

//confirmar cuenta
$router->get('/confirmar-cuenta',[ LoginController::class, 'confirmar']); 
$router->get('/mensaje',[ LoginController::class, 'mensaje']);

$router->get('/admin',[ LoginController::class, 'admin']);
$router->get('/cliente',[ LoginController::class, 'cliente']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();


