<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

final class ManualProviderAdapter implements AppointmentProviderInterface
{
    public function supports(array $source): bool
    {
        return ($source['provider'] ?? '') === 'manual';
    }

    public function fetchAppointmentTypes(array $source): array
    {
        return [];
    }

    public function fetchAvailableSlots(array $source): array
    {
        return [];
    }

    public function normalizeSlot(array $rawSlot): AppointmentSlotDTO
    {
        return new AppointmentSlotDTO((string) $rawSlot['starts_at'], $rawSlot['ends_at'] ?? null, (string) $rawSlot['booking_url'], $rawSlot['external_slot_id'] ?? null, null, null, hash('sha256', json_encode($rawSlot, JSON_THROW_ON_ERROR)), $rawSlot['source_label'] ?? null, $rawSlot);
    }

    public function validateSource(array $source): ProviderValidationResult
    {
        return new ProviderValidationResult(true, 'Manual source is valid.');
    }
}
