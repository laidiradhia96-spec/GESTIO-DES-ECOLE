<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'receipt_number' => $this->receipt_number,
            'period' => $this->period,
            'payment_type' => $this->payment_type,
            'amount_due' => $this->amount_due,
            'amount_paid' => $this->amount_paid,
            'remaining_amount' => $this->remaining_amount,
            'payment_method' => $this->payment_method,
            'payment_date' => $this->payment_date?->toDateString(),
            'payment_time' => $this->payment_time,
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
