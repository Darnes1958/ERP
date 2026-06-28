<?php

namespace App\Exceptions;

use Exception;

class FifoStockException extends Exception
{
    public static function forItem(string $itemName, $required, $available): self
    {
        return new self(
            "رصيد FIFO للصنف ({$itemName}) غير كافٍ. المطلوب: {$required}، المتاح في فواتير الشراء: {$available}"
        );
    }
}
