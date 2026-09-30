<?php

namespace App\Exceptions;

/**
 * A PTC stock movement was rejected by a business rule (over-receipt,
 * insufficient PTC stock, outstanding issuances, ...). The message is
 * written for the end user and is safe to flash.
 */
class PtcStockException extends \RuntimeException {}
