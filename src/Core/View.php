<?php

declare(strict_types=1);

namespace TerminRadar\Core;

final class View
{
    public function __construct(
        private readonly string $viewPath,
        private readonly Translator $translator,
        private readonly Session $session
    ) {
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = []): string
    {
        $view = $this;
        $t = fn (string $key): string => $this->translator->get($key);
        $e = fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $csrf = fn (): string => Csrf::token($this->session);
        $locale = $this->translator->locale();
        $flashSuccess = $this->session->pullFlash('success');
        $flashError = $this->session->pullFlash('error');

        extract($data, EXTR_SKIP);
        ob_start();
        require $this->viewPath . '/' . $template . '.php';
        $content = ob_get_clean();

        ob_start();
        require $this->viewPath . '/layouts/app.php';
        return (string) ob_get_clean();
    }
}
