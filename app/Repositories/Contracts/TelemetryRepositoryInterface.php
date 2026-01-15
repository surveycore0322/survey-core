<?php
namespace App\Repositories\Contracts;

interface TelemetryRepositoryInterface
{
    public function store(array $row): void;
}
