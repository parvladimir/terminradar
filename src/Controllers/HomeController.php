<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\SpecialtyRepository;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $specialties = (new SpecialtyRepository($this->app->database->pdo()))->active($this->app->translator->locale());
        return $this->view('home', ['specialties' => $specialties]);
    }

    public function legal(Request $request): Response
    {
        return $this->view('legal', ['slug' => trim($request->path, '/')]);
    }

    public function apiSpecialties(Request $request): Response
    {
        $specialties = (new SpecialtyRepository($this->app->database->pdo()))->active('de');
        return Response::json(['data' => $specialties]);
    }
}
