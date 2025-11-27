<?php

use App\Helpers\NumberHelper;

if (!function_exists('numberToWords')) {
    /**
     * Convert number to words
     * 
     * @param float|int $number
     * @return string
     */
    function numberToWords($number)
    {
        return NumberHelper::numberToWords($number);
    }
}

if (!function_exists('numberToWordsWithCurrency')) {
    /**
     * Convert number to words with currency
     * 
     * @param float|int $number
     * @param string $currency
     * @return string
     */
    function numberToWordsWithCurrency($number, $currency = 'Rupees')
    {
        return NumberHelper::numberToWordsWithCurrency($number, $currency);
    }
}

if (!function_exists('generateVoucherNumber')) {
    /**
     * Generate voucher number
     * 
     * @param int $id
     * @param string $prefix
     * @param int $padding
     * @return string
     */
    function generateVoucherNumber($id, $prefix = 'VCH', $padding = 4)
    {
        return NumberHelper::generateVoucherNumber($id, $prefix, $padding);
    }
}

if (!function_exists('generateVoucherByType')) {
    /**
     * Generate voucher number by type
     * 
     * @param int $id
     * @param string $type
     * @return string
     */
    function generateVoucherByType($id, $type = 'general')
    {
        return NumberHelper::generateVoucherByType($id, $type);
    }
}
