<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Next document numbers (F-16, D7: unique, gaps allowed). Every number is the highest existing
 * number of its series + 1, read with a locking read (SELECT ... FOR UPDATE) inside the caller's
 * transaction, so two saves never get the same number and deleting a record never makes its
 * number come back. Formats are unchanged:
 *
 *   order     SLE-O{NNN}-{YY}               (only when the user leaves Order No empty)
 *   job       {customer_no}/J{YY}-{NNN}      per customer and year
 *   delivery  SLE-D{NNN}-{YY}               (only when the user leaves Delivery No empty)
 *   issuance  I{YYMM}{NNNN}                  issuances, machine issues, material processing
 *   PTC       {YYMM}{NNN}
 *   adjust.   ADJ-{YYMM}{NNN}
 *   receipt   R{n}                           n-th receipt of one issuance
 *
 * Years / months are taken in Asia/Karachi (the business's clock; config app.timezone stays UTC).
 * $preview = true reads without a lock (to pre-fill a form); the saved number is taken again.
 */
class DocumentNumbers
{
    public const TIMEZONE = 'Asia/Karachi';

    public static function now(): Carbon
    {
        return Carbon::now(self::TIMEZONE);
    }

    public function nextOrderNo(bool $preview = false): string
    {
        $yy = self::now()->format('y');

        return $this->next('orders', 'order_no', 'SLE-O', "-{$yy}", 3, $preview);
    }

    public function nextJobNo(string $customerNo, bool $preview = false): string
    {
        return $this->next('orders', 'job_no', $customerNo.'/J'.self::now()->format('y').'-', '', 3, $preview);
    }

    public function nextDeliveryNo(bool $preview = false): string
    {
        $yy = self::now()->format('y');

        return $this->next('deliveries', 'delivery_no', 'SLE-D', "-{$yy}", 3, $preview);
    }

    public function nextIssuanceNo(bool $preview = false): string
    {
        return $this->next('stocks', 'stock_no', 'I'.self::now()->format('ym'), '', 4, $preview);
    }

    public function nextPtcNo(bool $preview = false): string
    {
        return $this->next('stocks', 'stock_no', self::now()->format('ym'), '', 3, $preview, fn ($q) => $q->where('is_ptc_master', 1));
    }

    public function nextAdjustmentNo(bool $preview = false): string
    {
        return $this->next('stocks', 'stock_no', 'ADJ-'.self::now()->format('ym'), '', 3, $preview);
    }

    /** R{n}: the n-th receipt against one (non-PTC) issuance. */
    public function nextIssuanceReceiptNo(int $issueId, bool $preview = false): string
    {
        return $this->next('stocks', 'stock_no', 'R', '', 1, $preview, fn ($q) => $q->where('issue_id', $issueId)->where('stock_type', 1));
    }

    /**
     * Highest "{prefix}{digits}{suffix}" in the column + 1, zero-padded to $width (wider numbers are
     * kept as they are). Values of another shape (typed by hand) are ignored.
     */
    private function next(string $table, string $column, string $prefix, string $suffix, int $width, bool $preview, ?\Closure $scope = null): string
    {
        if (! $preview && DB::transactionLevel() === 0) {
            throw new \LogicException('Document numbers must be taken inside the save\'s transaction.');
        }
        $query = DB::table($table)->where($column, 'like', str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $prefix).'%')
            ->when($scope, $scope);
        if (! $preview) {
            $query->lockForUpdate();
        }
        $pattern = '/^'.preg_quote($prefix, '/').'(\d+)'.preg_quote($suffix, '/').'$/';
        $max = 0;
        foreach ($query->pluck($column) as $value) {
            if (preg_match($pattern, (string) $value, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $prefix.str_pad((string) ($max + 1), $width, '0', STR_PAD_LEFT).$suffix;
    }
}
