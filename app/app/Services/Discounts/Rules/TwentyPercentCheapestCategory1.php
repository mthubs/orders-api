<?php

namespace App\Services\Discounts\Rules;

use App\Models\Order;
use App\Models\Product;
use App\Services\Discounts\DiscountRuleInterface;

class TwentyPercentCheapestCategory1 implements DiscountRuleInterface
{
    public function apply(Order $order): ?array
    {
        $category1Items = [];

        foreach ($order->items as $item) {
            $product = Product::find($item->pivot->product_id);
            if ($product && $product->category == 1) {
                $category1Items[] = ['price' => $product->price, 'quantity' => $item->pivot->quantity];
            }
        }

        if (count($category1Items) >= 2) {
            usort($category1Items, function($a, $b) {
                return $a['price'] <=> $b['price'];
            });
            $cheapestProduct = $category1Items[0];
            $discountAmount = $cheapestProduct['price'] * 0.20;

            return [[
                'discountReason' => '20_PERCENT_CATEGORY_1',
                'discountAmount' => number_format($discountAmount, 2, '.', ''),
                'subtotal' => number_format($order->total - $discountAmount, 2, '.', '')
            ]];
        }

        return [];
    }
}

