<?php
namespace App\UseCases\DTO;

final readonly class GetFormDetailOutput
{
    public function __construct(public FormDTO $form) {}
}
