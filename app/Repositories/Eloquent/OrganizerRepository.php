<?php
namespace App\Repositories\Eloquent;

use App\Models\Organizer;
use App\Repositories\Contracts\OrganizerRepositoryInterface;

final class OrganizerRepository implements OrganizerRepositoryInterface
{
    public function create(string $nickname): int
    {
        return Organizer::query()->create(['nickname' => $nickname])->id;
    }
}
