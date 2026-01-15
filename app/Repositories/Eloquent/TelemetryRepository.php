<?php
namespace App\Repositories\Eloquent;

use App\Models\TelemetryEvent;
use App\Repositories\Contracts\TelemetryRepositoryInterface;

final class TelemetryRepository implements TelemetryRepositoryInterface
{
    public function store(array $row): void
    {
        TelemetryEvent::query()->create([
            'event_name' => $row['event_name'],
            'form_id' => $row['form_id'],
            'view_id' => $row['view_id'],
            'participant_token_hash' => $row['participant_token_hash'],
            'server_ts' => $row['server_ts'],
            'client_ts' => $row['client_ts'],
            'device_type' => $row['device_type'],
            'locale' => $row['locale'],
            'user_agent' => $row['user_agent'],
            'properties' => $row['properties'],
        ]);
    }
}
