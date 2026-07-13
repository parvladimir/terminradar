<?php

declare(strict_types=1);

return static function (PDO $pdo): void {
    $now = date('c');
    $states = [
        ['BW', 'Baden-Württemberg'], ['BY', 'Bayern'], ['BE', 'Berlin'], ['BB', 'Brandenburg'], ['HB', 'Bremen'], ['HH', 'Hamburg'],
        ['HE', 'Hessen'], ['MV', 'Mecklenburg-Vorpommern'], ['NI', 'Niedersachsen'], ['NW', 'Nordrhein-Westfalen'], ['RP', 'Rheinland-Pfalz'],
        ['SL', 'Saarland'], ['SN', 'Sachsen'], ['ST', 'Sachsen-Anhalt'], ['SH', 'Schleswig-Holstein'], ['TH', 'Thüringen'],
    ];
    $insertState = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql'
        ? 'INSERT IGNORE INTO federal_states (code, name_de, created_at, updated_at) VALUES (:code, :name, :created_at, :updated_at)'
        : 'INSERT OR IGNORE INTO federal_states (code, name_de, created_at, updated_at) VALUES (:code, :name, :created_at, :updated_at)';
    $stmt = $pdo->prepare($insertState);
    foreach ($states as [$code, $name]) {
        $stmt->execute(['code' => $code, 'name' => $name, 'created_at' => $now, 'updated_at' => $now]);
    }

    $cities = [
        ['Dorsten', '46282', 'NW'], ['Marl', '45768', 'NW'], ['Essen', '45127', 'NW'], ['Gelsenkirchen', '45879', 'NW'],
        ['Recklinghausen', '45657', 'NW'], ['Düsseldorf', '40213', 'NW'], ['Köln', '50667', 'NW'], ['Dortmund', '44135', 'NW'],
        ['Berlin', '10115', 'BE'], ['Hamburg', '20095', 'HH'], ['München', '80331', 'BY'],
    ];
    $stmt = $pdo->prepare('INSERT INTO cities (name, postal_code, federal_state_code, is_test_data, created_at, updated_at) VALUES (:name, :postal_code, :state, 0, :created_at, :updated_at)');
    foreach ($cities as [$name, $postalCode, $state]) {
        $exists = $pdo->prepare('SELECT id FROM cities WHERE name = :name AND postal_code = :postal_code');
        $exists->execute(['name' => $name, 'postal_code' => $postalCode]);
        if (!$exists->fetch()) {
            $stmt->execute(['name' => $name, 'postal_code' => $postalCode, 'state' => $state, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
};
