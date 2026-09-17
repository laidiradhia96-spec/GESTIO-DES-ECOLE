<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
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
            'start_date' => $this->start_date?->toDateString(),
            'status' => $this->status,
            'payment_type' => $this->payment_type,
            'subject' => [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
                'code' => $this->subject->code,
            ],
            'teacher' => [
                'id' => $this->teacher->id,
                'full_name' => $this->teacher->first_name.' '.$this->teacher->last_name,
                'speciality' => $this->teacher->speciality,
            ],
            'school_year' => $this->whenLoaded('schoolYear', fn () => [
                'id' => $this->schoolYear->id,
                'name' => $this->schoolYear->name,
            ]),
        ];
    }
}
