<?php

namespace App\Exceptions;

/**
 * A product stock movement was rejected by a batch rule (not enough stock in
 * the allowed batches, batch reserved for another order, ...). The message is
 * written for the end user and is safe to flash.
 */
class StockBatchException extends \RuntimeException {}
