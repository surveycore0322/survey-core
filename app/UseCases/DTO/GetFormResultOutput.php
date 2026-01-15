<?php
namespace App\UseCases\DTO;

final readonly class GetFormResultOutput
{
    /** @param string[] $attend  @param string[] $absent */
    public function __construct(
        public int $formId,
        public string $title,
        public ?string $eventDate,
        public array $attend,
        public array $absent,
    ) {}
}
