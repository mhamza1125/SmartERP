<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\DeletionBlockedException;
use App\Support\DeletionImpact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shared delete flow (see deleteButton.blade.php):
 *  1. GET  {module}/{id}/delete-check -> deletionCheckResponse(): what would be blocked / deleted with it
 *  2. the page asks the user to confirm, listing those records
 *  3. DELETE {module}/{id} (confirmed=1) -> runDeletion(): rebuilds the impact, refuses when blocked,
 *     runs the repository delete inside one DB transaction
 * Controllers authorize('delete', ...) before calling either.
 */
trait HandlesDeletion
{
    protected function deletionCheckResponse(DeletionImpact $impact): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $impact->isBlocked() ? $impact->blockedMessage() : '',
            'data' => $impact->toArray(),
        ]);
    }

    /**
     * @param  callable  $delete  performs the delete (called inside a DB transaction)
     * @param  string  $redirectTo  URL to go to afterwards (the listing; the record's own page is gone)
     */
    protected function runDeletion(DeletionImpact $impact, callable $delete, string $redirectTo): RedirectResponse
    {
        if ($impact->isBlocked()) {
            return back()->with('fails', $impact->blockedMessage());
        }
        if (! request()->boolean('confirmed')) {
            return back()->with('fails', 'Deletion was not confirmed.');
        }

        try {
            DB::transaction(fn () => $delete());
        } catch (DeletionBlockedException $e) {
            return back()->with('fails', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Delete failed: '.$impact->label.': '.$e->getMessage(), ['exception' => $e]);

            return back()->with('fails', $impact->label.' could not be deleted. Nothing was changed.');
        }

        // Audit trail (Spatie activity log): what was deleted, by whom, and what went with it
        activity('delete')
            ->causedBy(auth()->user())
            ->withProperties(['cascades' => $impact->toArray()['cascades'], 'url' => request()->path()])
            ->log($impact->label.' deleted');

        return redirect($redirectTo)->with('success', $impact->label.' deleted successfully');
    }
}
