<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'date' => $this->date?->toDateString(),
            'status' => $this->status,
            'note' => $this->note,
            'subject' => $this->whenLoaded('subject', fn () => [
                'id' => $this->subject->id,
                'name' => $this->subject->name,
            ]),
            'teacher' => $this->whenLoaded('teacher', fn () => [
                'id' => $this->teacher->id,
                'full_name' => $this->teacher->first_name.' '.$this->teacher->last_name,
            ]),
            'school_year' => $this->whenLoaded('schoolYear', fn () => [
                'id' => $this->schoolYear->id,
                'name' => $this->schoolYear->name,
            ]),
        ];
    }
}
