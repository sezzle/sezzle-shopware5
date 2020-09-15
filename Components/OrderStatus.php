<?php

namespace SwagPaymentSezzle\Components;

final class OrderStatus
{
    /**
     * The default status for cancelled orders
     */
    const IN_PROGRESS = 1;
    /**
     * The default status for approved orders
     */
    const OPEN = 0;
}
