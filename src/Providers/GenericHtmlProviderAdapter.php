<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

final class GenericHtmlProviderAdapter extends AbstractHtmlProviderAdapter
{
    public function supports(array $source): bool
    {
        return ($source['provider'] ?? '') === 'generic_html';
    }

    public function normalizeSlot(array $rawSlot): AppointmentSlotDTO
    {
        return new AppointmentSlotDTO(
            (string) $rawSlot['starts_at'],
            $rawSlot['ends_at'] ?? null,
            (string) $rawSlot['booking_url'],
            $rawSlot['external_slot_id'] ?? null,
            $rawSlot['doctor_name'] ?? null,
            $rawSlot['appointment_type_name'] ?? null,
            hash('sha256', json_encode($rawSlot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)),
            $rawSlot['source_label'] ?? null,
            $rawSlot
        );
    }
}
