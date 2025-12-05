<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
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
            'user_id' => $this->user_id,
            'number' => $this->number,
            'balance' => number_format($this->balance, 2, ',', '.'),
            'status' => $this->status,
            'brand' => $this->brand,
            'expenses' => ExpenseResource::collection($this->whenLoaded('expenses')),
        ];
    }
}
