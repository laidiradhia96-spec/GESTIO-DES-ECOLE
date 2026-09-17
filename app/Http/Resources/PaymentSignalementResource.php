<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentSignalementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'period' => $this->period,
            'amount_remaining' => $this->amount_remaining,
            'status' => $this->status,
            'signalement_date' => $this->signalement_date?->toDateString(),
            'attendance_date' => $this->attendance_date?->toDateString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'note' => $this->note,
            'subject' => $this->whenLoaded('subject', fn () => [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ]),
            'school_year' => $this->whenLoaded('schoolYear', fn () => [
                'id' => $this->schoolYear->id,
                'name' => $this->schoolYear->name,
            ]),
        ];
    }
}
