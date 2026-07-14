<?php

use TerminRadar\Controllers\AdminController;
use TerminRadar\Controllers\ApiController;
use TerminRadar\Controllers\AuthController;
use TerminRadar\Controllers\DashboardController;
use TerminRadar\Controllers\HomeController;
use TerminRadar\Controllers\LocaleController;
use TerminRadar\Controllers\PracticeController;
use TerminRadar\Controllers\SlotController;
use TerminRadar\Controllers\WatchController;

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
    $router->get('/practices', [PracticeController::class, 'index']);
    $router->get('/practices/{id}', [PracticeController::class, 'show']);
    $router->get('/slots/{id}/book', [SlotController::class, 'book']);
    $router->get('/watches/create', [WatchController::class, 'create']);
    $router->post('/watches', [WatchController::class, 'store']);
    $router->post('/watches/{id}/pause', [WatchController::class, 'pause']);
    $router->post('/watches/{id}/resume', [WatchController::class, 'resume']);
    $router->post('/watches/{id}/delete', [WatchController::class, 'delete']);
    $router->get('/api/v1/watches', [WatchController::class, 'apiIndex']);

    $router->get('/admin', [AdminController::class, 'dashboard']);
    $router->get('/admin/practices', [AdminController::class, 'practices']);
    $router->post('/admin/practices', [AdminController::class, 'storePractice']);

    $router->post('/api/v1/auth/register', [ApiController::class, 'register']);
    $router->post('/api/v1/auth/login', [ApiController::class, 'login']);
    $router->post('/api/v1/auth/logout', [ApiController::class, 'logout']);
    $router->get('/api/v1/me', [ApiController::class, 'me']);
    $router->put('/api/v1/me', [ApiController::class, 'updateMe']);
    $router->get('/api/v1/me/export', [ApiController::class, 'exportMe']);
    $router->delete('/api/v1/me', [ApiController::class, 'deleteMe']);
    $router->get('/api/v1/specialties', [ApiController::class, 'specialties']);
    $router->get('/api/v1/practices', [ApiController::class, 'practices']);
    $router->get('/api/v1/practices/{id}', [ApiController::class, 'practice']);
    $router->get('/api/v1/watches', [ApiController::class, 'watches']);
    $router->post('/api/v1/watches', [ApiController::class, 'createWatch']);
    $router->get('/api/v1/slots', [ApiController::class, 'slots']);
};
