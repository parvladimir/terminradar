<?php

declare(strict_types=1);

namespace TerminRadar\Repositories;

use PDO;

final class SpecialtyRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return list<array<string, mixed>> */
    public function active(string $locale = 'uk'): array
    {
        $column = match ($locale) {
            'de' => 'name_de',
            'ru' => 'name_ru',
            default => 'name_uk',
        };
        $stmt = $this->pdo->query("SELECT id, slug, {$column} AS name, name_de, name_uk, name_ru FROM medical_specialties WHERE is_active = 1 ORDER BY {$column}");
        return $stmt->fetchAll();
    }
}
