<?php

namespace App\Services\Discounts\Rules;

use App\Models\Order;
use App\Services\Discounts\DiscountRuleInterface;

class TenPercentOver1000 implements DiscountRuleInterface
{
    public function apply(Order $order): ?array
    {
        if ($order->total >= 1000) {
            $discountAmount = $order->total * 0.10;

            return [[
                'discountReason' => '10_PERCENT_OVER_1000',
                'discountAmount' => number_format($discountAmount, 2, '.', ''),
                'subtotal' => number_format($order->total - $discountAmount, 2, '.', '')
            ]];
        }

        return [];
    }
}
