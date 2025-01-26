<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = [];

        foreach ($this->items as $item) {
            $items[] = [
                'productId' =>  $item->pivot->product_id,
                'quantity' =>  $item->pivot->quantity,
                'unitPrice' =>  number_format($item->pivot->unit_price, 2, '.', ''),
                'total' =>  number_format($item->pivot->quantity * $item->pivot->unit_price, 2, '.', '')
            ];
        }

        return [
            'id' => $this->id,
            'customerId' => $this->customer_id,
            'items' => $items,
            'total' => number_format(collect($items)->sum('total'), 2, '.', ''),
            'date' => Carbon::make($this->created_at)->format('d.m.Y H:i:s')
        ];
    }
}
