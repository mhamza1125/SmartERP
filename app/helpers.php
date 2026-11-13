<?php

use App\Helpers\NumberHelper;
use Carbon\Carbon;

if (! function_exists('formatDate')) {
    /**
     * Format a date for display as DD-MM-YYYY (e.g. 30-09-2026).
     * Null, empty, zero or unparseable dates return $default.
     * Display only - never use for form values or stored dates.
     *
     * @param  mixed  $date
     * @param  string  $default
     * @param  string  $format
     * @return string
     */
    function formatDate($date, $default = '-', $format = 'd-m-Y')
    {
        if ($date === null || $date === '' || str_starts_with((string) $date, '0000-00-00')) {
            return $default;
        }

        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (! function_exists('formatDateTime')) {
    /**
     * Format a date-time for display as DD-MM-YYYY HH:MM.
     *
     * @param  mixed  $date
     * @param  string  $default
     * @return string
     */
    function formatDateTime($date, $default = '-')
    {
        return formatDate($date, $default, 'd-m-Y h:i A');
    }
}

if (! function_exists('numberToWords')) {
    /**
     * Convert number to words
     *
     * @param  float|int  $number
     * @return string
     */
    function numberToWords($number)
    {
        return NumberHelper::numberToWords($number);
    }
}

if (! function_exists('numberToWordsWithCurrency')) {
    /**
     * Convert number to words with currency
     *
     * @param  float|int  $number
     * @param  string  $currency
     * @return string
     */
    function numberToWordsWithCurrency($number, $currency = 'Rupees')
    {
        return NumberHelper::numberToWordsWithCurrency($number, $currency);
    }
}

if (! function_exists('generateVoucherNumber')) {
    /**
     * Generate voucher number
     *
     * @param  int  $id
     * @param  string  $prefix
     * @param  int  $padding
     * @return string
     */
    function generateVoucherNumber($id, $prefix = 'VCH', $padding = 4)
    {
        return NumberHelper::generateVoucherNumber($id, $prefix, $padding);
    }
}

if (! function_exists('generateVoucherByType')) {
    /**
     * Generate voucher number by type
     *
     * @param  int  $id
     * @param  string  $type
     * @return string
     */
    function generateVoucherByType($id, $type = 'general')
    {
        return NumberHelper::generateVoucherByType($id, $type);
    }
}

if (! function_exists('printLogoUrl')) {
    /**
     * URL of the logo for printed documents, built from the request's own base URL
     * (works under a sub-folder such as http://host:8080/ERP/public).
     * Company logo when its file exists, otherwise the bundled default logo.
     */
    function printLogoUrl(?\App\Models\Company $company = null): string
    {
        $company ??= \App\Models\Company::first();

        foreach ([$company?->logo_path, $company?->logo ? 'storage/'.$company->logo : null] as $path) {
            $path = ltrim((string) $path, '/');
            if ($path !== '' && is_file(public_path($path))) {
                return asset($path);
            }
        }

        return asset('assets/print-logo.png');
    }
}

if (! function_exists('transactionLabel')) {
    /**
     * Display name of a transactions.transaction_type or transaction_to value
     * ("generalVoucher" -> "Journal Voucher", "advance" -> "Loan Given", "vendor" -> "Vendor").
     */
    function transactionLabel(?string $value): string
    {
        $labels = \App\Repositories\TransactionRepository::TYPE_LABELS + ['admin' => 'Opening Balance'];

        return $labels[$value] ?? ucfirst((string) $value);
    }
}
