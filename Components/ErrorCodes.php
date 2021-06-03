<?php

namespace SezzlePayment\Components;

final class ErrorCodes
{
    const CANCELED_BY_USER = 1;
    const COMMUNICATION_FAILURE = 2;
    const NO_ORDER_TO_PROCESS = 3;
    const UNKNOWN = 4;
    const COMMUNICATION_FAILURE_FINISH = 5;
    const BASKET_VALIDATION_ERROR = 6;
    const ADDRESS_VALIDATION_ERROR = 7;
    const NO_DISPATCH_FOR_ORDER = 8;
}
