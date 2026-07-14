<?php

declare(strict_types=1);

namespace TerminRadar\Providers;

final class AppointmentSlotDTO
{
    public function __construct(
        public readonly string $startsAt,
        public readonly ?string $endsAt,
        public readonly string $bookingUrl,
        public readonly ?string $externalSlotId,
        public readonly ?string $doctorName,
        public readonly ?string $appointmentTypeName,
        public readonly string $rawHash,
        public readonly ?string $sourceLabel = null,
        public readonly ?array $rawPayload = null
    ) {
    }
}
