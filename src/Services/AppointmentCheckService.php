<?php

declare(strict_types=1);

namespace TerminRadar\Services;

use PDO;
use TerminRadar\Providers\ProviderFetchException;
use TerminRadar\Providers\ProviderRegistry;
use TerminRadar\Repositories\AppointmentSlotRepository;
use TerminRadar\Repositories\AppointmentSourceRepository;
use TerminRadar\Repositories\ProviderLogRepository;

final class AppointmentCheckService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array{checked:int, new:int, updated:int, disappeared:int, errors:int} */
    public function checkDue(): array
    {
        $sources = (new AppointmentSourceRepository($this->pdo))->due();
        $totals = ['checked' => 0, 'new' => 0, 'updated' => 0, 'disappeared' => 0, 'errors' => 0];

        foreach ($sources as $source) {
            $result = $this->checkSource((int) $source['id']);
            foreach ($totals as $key => $value) {
                $totals[$key] += $result[$key] ?? 0;
            }
        }

        return $totals;
    }

    /** @return array{checked:int, new:int, updated:int, disappeared:int, errors:int} */
    public function checkSource(int $sourceId): array
    {
        $sources = new AppointmentSourceRepository($this->pdo);
        $slots = new AppointmentSlotRepository($this->pdo);
        $logs = new ProviderLogRepository($this->pdo);
        $source = $sources->find($sourceId);

        if ($source === null) {
            throw new ProviderFetchException('Appointment source not found.');
        }

        if (!$sources->acquireLock($sourceId)) {
            return ['checked' => 0, 'new' => 0, 'updated' => 0, 'disappeared' => 0, 'errors' => 0];
        }

        $started = microtime(true);
        try {
            $adapter = (new ProviderRegistry())->forSource($source);
            $rawSlots = $adapter->fetchAvailableSlots($source);
            $normalized = array_map(static fn (array $raw) => $adapter->normalizeSlot($raw), $rawSlots);
            $sync = $slots->sync($sourceId, $normalized);
            (new WatchMatchingService($this->pdo))->matchSource($sourceId);
            $sources->markSuccess($sourceId);
            $logs->create($sourceId, 'success', 200, count($normalized), (int) ((microtime(true) - $started) * 1000), null, hash('sha256', json_encode($rawSlots, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)));

            return ['checked' => 1, 'new' => $sync['new'], 'updated' => $sync['updated'], 'disappeared' => $sync['disappeared'], 'errors' => 0];
        } catch (ProviderFetchException $exception) {
            $sources->markError($sourceId, $exception->getMessage());
            $logs->create($sourceId, 'error', $exception->httpStatus, 0, (int) ((microtime(true) - $started) * 1000), $exception->getMessage(), null);
            return ['checked' => 1, 'new' => 0, 'updated' => 0, 'disappeared' => 0, 'errors' => 1];
        } finally {
            $sources->releaseLock($sourceId);
        }
    }
}
