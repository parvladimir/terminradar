<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Request;
use TerminRadar\Core\Response;
use TerminRadar\Repositories\SlotRepository;

final class SlotController extends Controller
{
    public function book(Request $request, string $id): Response
    {
        $slot = (new SlotRepository($this->app->database->pdo()))->findWithPractice((int) $id);
        if ($slot === null) {
            return new Response($this->app->view->render('errors/404', ['path' => $request->path]), 404);
        }

        return $this->view('slots/book', ['slot' => $slot]);
    }
}
