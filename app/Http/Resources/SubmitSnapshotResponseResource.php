<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubmitSnapshotResponseResource extends JsonResource
{
    public function toArray($request): array
    {
        // $this は SubmitSnapshotOutput を想定
        return [
            'snapshot_id' => $this->snapshotId,
            'participant_token' => $this->participantToken,
            'submitted_at' => $this->submittedAt,
        ];
    }
}
