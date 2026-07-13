<?php

declare(strict_types=1);

namespace TerminRadar\Controllers;

use TerminRadar\Core\Csrf;
use TerminRadar\Core\Request;
use TerminRadar\Core\Response;

final class LocaleController extends Controller
{
    public function switch(Request $request): Response
    {
        if (!Csrf::validate($this->app->session, $request->input('_token'))) {
            $this->app->session->flash('error', 'Invalid CSRF token.');
            return Response::redirect('/');
        }
        $this->app->translator->setLocale((string) $request->input('locale', 'uk'));
        return Response::redirect((string) ($request->input('redirect') ?: '/'));
    }
}
