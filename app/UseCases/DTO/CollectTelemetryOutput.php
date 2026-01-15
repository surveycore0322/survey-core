<?php
namespace App\UseCases\DTO;

final readonly class CollectTelemetryOutput
{
    public function __construct(public bool $accepted = true) {}
}
