<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

interface AppointmentProviderInterface
{
    public function supports(array $source): bool;

    /** @return list<array<string, mixed>> */
    public function fetchAppointmentTypes(array $source): array;

    /** @return list<array<string, mixed>> */
    public function fetchAvailableSlots(array $source): array;

    /** @param array<string, mixed> $rawSlot */
    public function normalizeSlot(array $rawSlot): AppointmentSlotDTO;

    public function validateSource(array $source): ProviderValidationResult;
}
