<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $now = date('c');
    $rows = [
        ['hausarzt', 'Hausarzt', 'Сімейний лікар', 'Семейный врач'],
        ['orthopaedie', 'Orthopädie', 'Ортопедія', 'Ортопедия'],
        ['urologie', 'Urologie', 'Урологія', 'Урология'],
        ['gynaekologie', 'Gynäkologie', 'Гінекологія', 'Гинекология'],
        ['dermatologie', 'Dermatologie', 'Дерматологія', 'Дерматология'],
        ['neurologie', 'Neurologie', 'Неврологія', 'Неврология'],
        ['kardiologie', 'Kardiologie', 'Кардіологія', 'Кардиология'],
        ['hno', 'HNO', 'ЛОР', 'ЛОР'],
        ['augenheilkunde', 'Augenheilkunde', 'Офтальмологія', 'Офтальмология'],
        ['kinderarzt', 'Kinderarzt', 'Педіатр', 'Педиатр'],
        ['zahnarzt', 'Zahnarzt', 'Стоматолог', 'Стоматолог'],
        ['radiologie', 'Radiologie', 'Радіологія', 'Радиология'],
        ['psychotherapie', 'Psychotherapie', 'Психотерапія', 'Психотерапия'],
        ['chirurgie', 'Chirurgie', 'Хірургія', 'Хирургия'],
        ['gastroenterologie', 'Gastroenterologie', 'Гастроентерологія', 'Гастроэнтерология'],
        ['endokrinologie', 'Endokrinologie', 'Ендокринологія', 'Эндокринология'],
        ['allergologie', 'Allergologie', 'Алергологія', 'Аллергология'],
        ['physiotherapie', 'Physiotherapie', 'Фізіотерапія', 'Физиотерапия'],
    ];

    $stmt = $pdo->prepare('INSERT OR IGNORE INTO medical_specialties (slug, name_de, name_uk, name_ru, is_active, created_at, updated_at) VALUES (:slug, :de, :uk, :ru, 1, :created_at, :updated_at)');
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        $stmt = $pdo->prepare('INSERT IGNORE INTO medical_specialties (slug, name_de, name_uk, name_ru, is_active, created_at, updated_at) VALUES (:slug, :de, :uk, :ru, 1, :created_at, :updated_at)');
    }
    foreach ($rows as [$slug, $de, $uk, $ru]) {
        $stmt->execute(['slug' => $slug, 'de' => $de, 'uk' => $uk, 'ru' => $ru, 'created_at' => $now, 'updated_at' => $now]);
    }
};
