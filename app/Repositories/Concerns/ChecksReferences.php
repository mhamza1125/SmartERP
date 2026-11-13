<?php

namespace App\Repositories\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Small helpers for deletionImpact(): count rows of another table that point at a record,
 * and pick a few of their document numbers to show the user.
 */
trait ChecksReferences
{
    /**
     * Rows of $table where $column = $id (or IN $id), narrowed further by $scope.
     */
    protected function refQuery(string $table, string $column, $id, ?callable $scope = null): Builder
    {
        $query = DB::table($table);
        is_array($id) ? $query->whereIn($column, $id) : $query->where($column, $id);
        if ($scope) {
            $scope($query);
        }

        return $query;
    }

    protected function refCount(string $table, string $column, $id, ?callable $scope = null): int
    {
        return $this->refQuery($table, $column, $id, $scope)->count();
    }

    /**
     * Up to six values of $labelColumn from the referencing rows (document numbers for the message).
     */
    protected function refExamples(string $table, string $column, $id, string $labelColumn, ?callable $scope = null): array
    {
        return $this->refQuery($table, $column, $id, $scope)
            ->whereNotNull($labelColumn)
            ->orderBy($labelColumn)
            ->limit(6)
            ->pluck($labelColumn)
            ->all();
    }
}
