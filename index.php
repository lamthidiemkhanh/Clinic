<?php
// Simple MVC front controller
declare(strict_types=1);

// Autoload very small tree
spl_autoload_register(function($class){
    $base = __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR;
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $paths = [
        $base . $class . '.php',
        $base . 'controllers' . DIRECTORY_SEPARATOR . $class . '.php',
        $base . 'models' . DIRECTORY_SEPARATOR . $class . '.php',
        $base . 'core' . DIRECTORY_SEPARATOR . $class . '.php',
    ];
    foreach($paths as $p){ if (is_file($p)) { require_once $p; return; } }
});

require_once __DIR__ . '/app/core/helpers.php';

$page = $_GET['page'] ?? 'home';

function controller($name){
    // Try simple, then StudlyCase converted from dash/underscore
    $candidates = [];
    $candidates[] = ucfirst($name) . 'Controller';
    $studly = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    $candidates[] = $studly . 'Controller';
    foreach ($candidates as $class) {
        if (class_exists($class)) return new $class();
    }
    http_response_code(404);
    echo "Controller not found"; exit;
}

switch ($page) {
    case 'home':
        controller('home')->index();
        break;
    case 'booking':
        controller('booking')->index();
        break;
    case 'notifications':
        controller('notifications')->index();
        break;
    case 'appointments':
        controller('appointments')->index();
        break;
    case 'settings':
        controller('settings')->index();
        break;
    case 'search':
        controller('search')->index();
        break;
    case 'clinic-detail':
        controller('clinic-detail')->index();
        break;
    case 'admin':
        // Temporarily disable admin home; redirect to user home
        header('Location: index.php?page=home');
        exit;
        case 'api.clinic':
            $controller = new Api_ClinicController();
            $controller->index();
            break;
        case 'api.seed_clinic':
            (new Api_SeedClinicController())->handle();
            break;
    case 'api.appointments':
        (new Api_AppointmentsController())->handle();
        break;
        case 'api.service':
            if (!class_exists('Api_ServiceController')) require_once __DIR__ . '/app/controllers/Api/ServiceController.php';
            (new Api_ServiceController())->handle();
            break;
        case 'api.category_service':
            if (!class_exists('Api_CategoryServiceController')) require_once __DIR__ . '/app/controllers/Api/CategoryServiceController.php';
            (new Api_CategoryServiceController())->handle();
            break;
    case 'api.role':
        (new Api_RoleController())->handle();
        break;
    case 'api.user':
        (new Api_UserController())->handle();
        break;
    case 'api.animal_species':
        (new Api_AnimalSpeciesController())->handle();
        break;
    case 'api.animal_breed':
        (new Api_AnimalBreedController())->handle();
        break;
    case 'api.pet_type_service':
        (new Api_PetTypeServiceController())->handle();
        break;
    case 'api.pet':
        (new Api_PetController())->handle();
        break;
    default:
        http_response_code(404);
        view('errors/404', [ 'title' => '404', 'pageId' => 'not-found' ]);
}
