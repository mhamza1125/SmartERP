<?php

namespace App\Exceptions;

/**
 * A material stock-out would take a material below 0 (negative stock is never allowed).
 * Extends StockBatchException so every save that already refuses a product shortfall
 * (rollback + flash the message) refuses a material shortfall the same way.
 * The message is written for the end user and is safe to flash.
 */
class MaterialStockException extends StockBatchException {}
