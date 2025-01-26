<?php

namespace App\Services\Discounts;

use App\Models\Order;
use App\Services\Discounts\Rules\Buy6Get1Free;
use App\Services\Discounts\Rules\TenPercentOver1000;
use App\Services\Discounts\Rules\TwentyPercentCheapestCategory1;

class DiscountManager
{
    protected array $rules = [
        Buy6Get1Free::class,
        TenPercentOver1000::class,
        TwentyPercentCheapestCategory1::class,
    ];

    public function applyDiscounts(Order $order): array
    {
        $discounts = [];

        foreach ($this->rules as $ruleClass) {
            $rule = new $ruleClass();
            $result = $rule->apply($order);
            if (!empty($result)) {
                $discounts = array_merge($discounts, $result);
            }
        }

        return [
            'orderId' => $order->id,
            'discounts' => $discounts,
            'totalDiscount' => number_format(array_sum(array_column($discounts, 'discountAmount')), 2, '.', ''),
            'discountedTotal' => number_format($order['total'], 2, '.', '')
        ];
    }
}
