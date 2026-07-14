<?php

declare(strict_types=1);

return static function (PDO $pdo, string $driver): void {
    $auto = $driver === 'mysql' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $json = $driver === 'mysql' ? 'JSON' : 'TEXT';
    $bool = $driver === 'mysql' ? 'TINYINT(1)' : 'INTEGER';
    $text = $driver === 'mysql' ? 'TEXT' : 'TEXT';
    $string = $driver === 'mysql' ? 'VARCHAR(191)' : 'TEXT';
    $datetime = $driver === 'mysql' ? 'DATETIME NULL' : 'TEXT NULL';
    $notNullDate = $driver === 'mysql' ? 'DATETIME NOT NULL' : 'TEXT NOT NULL';

    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (id {$auto}, migration {$string} NOT NULL UNIQUE, ran_at {$notNullDate})");

    $pdo->exec("CREATE TABLE IF NOT EXISTS medical_specialties (
        id {$auto}, slug {$string} NOT NULL UNIQUE, name_de {$string} NOT NULL, name_uk {$string} NOT NULL, name_ru {$string} NOT NULL,
        is_active {$bool} NOT NULL DEFAULT 1, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS federal_states (
        id {$auto}, code {$string} NOT NULL UNIQUE, name_de {$string} NOT NULL, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS cities (
        id {$auto}, name {$string} NOT NULL, postal_code {$string} NULL, federal_state_code {$string} NULL, is_test_data {$bool} NOT NULL DEFAULT 0, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS practices (
        id {$auto}, name {$string} NOT NULL, slug {$string} NOT NULL UNIQUE, description {$text} NULL, street {$string} NULL, house_number {$string} NULL,
        postal_code {$string} NULL, city {$string} NULL, latitude DECIMAL(10,7) NULL, longitude DECIMAL(10,7) NULL, phone {$string} NULL, email {$string} NULL,
        website_url {$string} NULL, booking_url {$string} NULL, source_provider {$string} NULL, source_external_id {$string} NULL,
        insurance_types {$json} NULL, languages {$json} NULL, wheelchair_accessible {$bool} NULL, is_verified {$bool} NOT NULL DEFAULT 0, is_active {$bool} NOT NULL DEFAULT 1,
        is_test_data {$bool} NOT NULL DEFAULT 0, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS doctors (
        id {$auto}, practice_id INTEGER NOT NULL, title {$string} NULL, first_name {$string} NULL, last_name {$string} NULL, display_name {$string} NOT NULL,
        gender {$string} NULL, biography {$text} NULL, photo_url {$string} NULL, languages {$json} NULL, insurance_types {$json} NULL,
        source_provider {$string} NULL, source_external_id {$string} NULL, is_verified {$bool} NOT NULL DEFAULT 0, is_active {$bool} NOT NULL DEFAULT 1,
        created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS doctor_specialty (doctor_id INTEGER NOT NULL, specialty_id INTEGER NOT NULL, PRIMARY KEY (doctor_id, specialty_id))");

    $pdo->exec("CREATE TABLE IF NOT EXISTS appointment_types (
        id {$auto}, practice_id INTEGER NOT NULL, doctor_id INTEGER NULL, provider_id {$string} NULL, external_type_id {$string} NULL, name {$string} NOT NULL,
        description {$text} NULL, minimum_age INTEGER NULL, maximum_age INTEGER NULL, insurance_type {$string} NULL, new_patients_allowed {$bool} NULL,
        referral_required {$bool} NULL, is_active {$bool} NOT NULL DEFAULT 1, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS appointment_sources (
        id {$auto}, practice_id INTEGER NOT NULL, provider {$string} NOT NULL, source_url {$string} NOT NULL, external_calendar_id {$string} NULL,
        adapter_class {$string} NOT NULL, check_interval_minutes INTEGER NOT NULL DEFAULT 15, enabled {$bool} NOT NULL DEFAULT 0,
        last_success_at {$datetime}, last_error_at {$datetime}, last_error_message {$text} NULL, consecutive_failures INTEGER NOT NULL DEFAULT 0,
        created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS appointment_slots (
        id {$auto}, appointment_source_id INTEGER NOT NULL, appointment_type_id INTEGER NULL, doctor_id INTEGER NULL, starts_at {$notNullDate}, ends_at {$datetime},
        booking_url {$string} NOT NULL, external_slot_id {$string} NULL, first_seen_at {$notNullDate}, last_seen_at {$notNullDate}, disappeared_at {$datetime},
        status {$string} NOT NULL DEFAULT 'available', raw_hash {$string} NOT NULL, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id {$auto}, name {$string} NOT NULL, email {$string} NOT NULL UNIQUE, email_verified_at {$datetime}, password_hash {$string} NOT NULL, role {$string} NOT NULL DEFAULT 'user',
        locale {$string} NOT NULL DEFAULT 'uk', timezone {$string} NOT NULL DEFAULT 'Europe/Berlin', telegram_chat_id {$string} NULL, telegram_verified_at {$datetime},
        web_push_enabled {$bool} NOT NULL DEFAULT 0, email_notifications_enabled {$bool} NOT NULL DEFAULT 1, consent_at {$datetime}, privacy_version {$string} NULL,
        created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS watches (
        id {$auto}, user_id INTEGER NOT NULL, name {$string} NOT NULL, specialty_id INTEGER NULL, practice_id INTEGER NULL, doctor_id INTEGER NULL,
        appointment_type_id INTEGER NULL, city {$string} NULL, postal_code {$string} NULL, radius_km INTEGER NULL, earliest_date DATE NULL, latest_date DATE NULL,
        desired_before_date DATE NULL, allowed_weekdays {$json} NULL, time_from TIME NULL, time_to TIME NULL, insurance_type {$string} NULL,
        only_new_patients {$bool} NOT NULL DEFAULT 0, frequency_minutes INTEGER NOT NULL DEFAULT 15, notification_email {$bool} NOT NULL DEFAULT 1,
        notification_telegram {$bool} NOT NULL DEFAULT 0, notification_web_push {$bool} NOT NULL DEFAULT 0, active {$bool} NOT NULL DEFAULT 1,
        last_checked_at {$datetime}, expires_at {$datetime}, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS watch_matches (
        id {$auto}, watch_id INTEGER NOT NULL, appointment_slot_id INTEGER NOT NULL, matched_at {$notNullDate}, notification_sent_at {$datetime},
        notification_channel {$string} NULL, status {$string} NOT NULL DEFAULT 'new', created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id {$auto}, user_id INTEGER NOT NULL, watch_id INTEGER NULL, appointment_slot_id INTEGER NULL, channel {$string} NOT NULL, subject {$string} NOT NULL,
        body {$text} NOT NULL, status {$string} NOT NULL DEFAULT 'pending', sent_at {$datetime}, error_message {$text} NULL, created_at {$notNullDate}, updated_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS provider_logs (
        id {$auto}, appointment_source_id INTEGER NOT NULL, status {$string} NOT NULL, http_status INTEGER NULL, appointments_found INTEGER NOT NULL DEFAULT 0,
        duration_ms INTEGER NOT NULL DEFAULT 0, error_message {$text} NULL, response_hash {$string} NULL, created_at {$notNullDate}
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS source_locks (
        appointment_source_id INTEGER NOT NULL PRIMARY KEY, locked_until {$notNullDate}, owner {$string} NOT NULL, created_at {$notNullDate}, updated_at {$notNullDate}
    )");
};
