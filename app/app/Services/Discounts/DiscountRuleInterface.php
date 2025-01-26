<?php

namespace App\Services\Discounts;

use App\Models\Order;

interface DiscountRuleInterface
{
    public function apply(Order $order): ?array;
}
