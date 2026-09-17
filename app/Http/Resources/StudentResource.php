<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name.' '.$this->last_name,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'level' => $this->level,
            'phone' => $this->phone,
            'address' => $this->address,
            'enrollments' => EnrollmentResource::collection(
                $this->whenLoaded('enrollments')
            ),
            'enrollments_count' => $this->whenCounted('enrollments'),
        ];
    }
}
