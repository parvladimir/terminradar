<?php

use TerminRadar\Controllers\AdminController;
use TerminRadar\Controllers\AuthController;
use TerminRadar\Controllers\DashboardController;
use TerminRadar\Controllers\HomeController;
use TerminRadar\Controllers\LocaleController;

return static function (TerminRadar\Core\Router $router): void {
    $router->get('/', [HomeController::class, 'index']);
    $router->get('/impressum', [HomeController::class, 'legal']);
    $router->get('/datenschutz', [HomeController::class, 'legal']);
    $router->get('/terms', [HomeController::class, 'legal']);
    $router->get('/cookies', [HomeController::class, 'legal']);
    $router->get('/haftung', [HomeController::class, 'legal']);

    $router->post('/locale', [LocaleController::class, 'switch']);

    $router->get('/register', [AuthController::class, 'showRegister']);
    $router->post('/register', [AuthController::class, 'register']);
    $router->get('/login', [AuthController::class, 'showLogin']);
    $router->post('/login', [AuthController::class, 'login']);
    $router->post('/logout', [AuthController::class, 'logout']);

    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/admin', [AdminController::class, 'dashboard']);

    $router->get('/api/v1/specialties', [HomeController::class, 'apiSpecialties']);
};
