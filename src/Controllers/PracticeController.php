<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\PracticeRepository;
use TerminRadar\Repositories\SpecialtyRepository;

final class PracticeController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'specialty' => (string) $request->input('specialty', ''),
            'city' => (string) $request->input('city', ''),
            'q' => (string) $request->input('q', ''),
            'insurance' => (string) $request->input('insurance', ''),
            'language' => (string) $request->input('language', ''),
        ];

        $locale = $this->app->translator->locale();
        $practices = (new PracticeRepository($this->app->database->pdo()))->search($filters, $locale);
        $specialties = (new SpecialtyRepository($this->app->database->pdo()))->active($locale);

        return $this->view('practices/index', [
            'filters' => $filters,
            'practices' => $practices,
            'specialties' => $specialties,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $practice = (new PracticeRepository($this->app->database->pdo()))->find((int) $id, $this->app->translator->locale());
        if ($practice === null) {
            return new Response($this->app->view->render('errors/404', ['path' => $request->path]), 404);
        }

        return $this->view('practices/show', ['practice' => $practice]);
    }

    public function apiIndex(Request $request): Response
    {
        $practices = (new PracticeRepository($this->app->database->pdo()))->search([
            'specialty' => (string) $request->input('specialty', ''),
            'city' => (string) $request->input('city', ''),
            'q' => (string) $request->input('q', ''),
        ], 'de');

        return Response::json(['data' => $practices]);
    }
}
