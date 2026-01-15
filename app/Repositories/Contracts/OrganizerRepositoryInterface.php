<?php
namespace App\Repositories\Contracts;

interface OrganizerRepositoryInterface
{
    public function create(string $nickname): int;
}
