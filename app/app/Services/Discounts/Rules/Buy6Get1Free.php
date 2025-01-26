<?php

namespace App\Services\Discounts\Rules;

use App\Models\Order;
use App\Models\Product;
use App\Services\Discounts\DiscountRuleInterface;

class Buy6Get1Free implements DiscountRuleInterface
{
    public function apply(Order $order): ?array
    {
        $discounts = [];

        foreach ($order->items as $item) {
            $product = Product::find($item->pivot->product_id);
            if ($product && $product->category == 2 && $item->pivot->quantity >= 6) {
                $freeItems = floor($item->pivot->quantity / 6);
                $discountAmount = $freeItems * $product->price;

                $discounts[] = [
                    'discountReason' => 'BUY_6_GET_1',
                    'discountAmount' => number_format($discountAmount, 2, '.', ''),
                    'subtotal' => number_format($order->total - $discountAmount, 2, '.', '')
                ];

                $order->total -= $discountAmount;
            }
        }

        return $discounts;
    }
}
