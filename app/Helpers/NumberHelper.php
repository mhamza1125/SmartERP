<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Convert a number to words (English)
     * 
     * @param float|int $number
     * @return string
     */
    public static function numberToWords($number)
    {
        if (!is_numeric($number)) {
            return 'Invalid Number';
        }

        // Handle negative numbers
        if ($number < 0) {
            return 'Negative ' . self::numberToWords(abs($number));
        }

        // Handle zero
        if ($number == 0) {
            return 'Zero';
        }

        // Split into integer and decimal parts
        $parts = explode('.', number_format($number, 2, '.', ''));
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) $parts[1] : 0;

        $result = '';

        // Convert integer part
        if ($integerPart > 0) {
            $result = self::convertIntegerToWords($integerPart);
        }

        // Convert decimal part (for currency: paise/cents)
        if ($decimalPart > 0) {
            if ($result !== '') {
                $result .= ' and ';
            }
            $result .= self::convertIntegerToWords($decimalPart);
            $result .= $decimalPart == 1 ? ' Paisa' : ' Paise';
        }

        return $result;
    }

    /**
     * Convert integer to words
     * 
     * @param int $number
     * @return string
     */
    private static function convertIntegerToWords($number)
    {
        if ($number == 0) {
            return '';
        }

        $ones = [
            '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
            'Seventeen', 'Eighteen', 'Nineteen'
        ];

        $tens = [
            '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'
        ];

        $result = '';

        // Handle crores (10,000,000)
        if ($number >= 10000000) {
            $crores = intval($number / 10000000);
            $result .= self::convertIntegerToWords($crores) . ' Crore ';
            $number %= 10000000;
        }

        // Handle lakhs (100,000)
        if ($number >= 100000) {
            $lakhs = intval($number / 100000);
            $result .= self::convertIntegerToWords($lakhs) . ' Lakh ';
            $number %= 100000;
        }

        // Handle thousands (1,000)
        if ($number >= 1000) {
            $thousands = intval($number / 1000);
            $result .= self::convertIntegerToWords($thousands) . ' Thousand ';
            $number %= 1000;
        }

        // Handle hundreds (100)
        if ($number >= 100) {
            $hundreds = intval($number / 100);
            $result .= $ones[$hundreds] . ' Hundred ';
            $number %= 100;
        }

        // Handle tens and ones
        if ($number >= 20) {
            $tensDigit = intval($number / 10);
            $onesDigit = $number % 10;
            $result .= $tens[$tensDigit];
            if ($onesDigit > 0) {
                $result .= ' ' . $ones[$onesDigit];
            }
        } elseif ($number > 0) {
            $result .= $ones[$number];
        }

        return trim($result);
    }

    /**
     * Convert number to words with currency format
     *
     * @param float|int $number
     * @param string $currency
     * @return string
     */
    public static function numberToWordsWithCurrency($number, $currency = 'Rupees')
    {
        $words = self::numberToWords($number);

        if ($words === 'Zero' || $words === 'Invalid Number') {
            return $words;
        }

        // Add currency at the beginning if there's an integer part
        if (!str_contains($words, 'and')) {
            // Only decimal part
            return $words;
        } else {
            // Has integer part
            $parts = explode(' and ', $words);
            return $currency . ' ' . $parts[0] . (isset($parts[1]) ? ' and ' . $parts[1] : '');
        }
    }

    /**
     * Generate voucher number based on ID
     *
     * @param int $id
     * @param string $prefix
     * @param int $padding
     * @return string
     */
    public static function generateVoucherNumber($id, $prefix = 'VCH', $padding = 4)
    {
        return $prefix . '-' . str_pad($id, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * Generate different types of voucher numbers
     *
     * @param int $id
     * @param string $type
     * @return string
     */
    public static function generateVoucherByType($id, $type = 'general')
    {
        switch (strtolower($type)) {
            case 'payment':
                return self::generateVoucherNumber($id, 'PAY');
            case 'receipt':
                return self::generateVoucherNumber($id, 'RCP');
            case 'invoice':
                return self::generateVoucherNumber($id, 'INV');
            case 'delivery':
                return self::generateVoucherNumber($id, 'DEL');
            case 'purchase':
                return self::generateVoucherNumber($id, 'PUR');
            case 'order':
                return self::generateVoucherNumber($id, 'ORD');
            default:
                return self::generateVoucherNumber($id, 'VCH');
        }
    }
}
