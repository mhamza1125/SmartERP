<?php

namespace App\Http\Controllers;

use App\Http\Requests\DataResetRequest;
use App\Models\Settings;
use App\Support\TransactionalDataReset;
use Illuminate\Support\Facades\Log;

/**
 * TEMPORARY one-time cleanup: Settings > Reset Test Data. Admin only (is_admin route middleware +
 * SettingsPolicy::resetData, which only the Admin role passes). Remove after the cleanup is done.
 */
class DataResetController extends Controller
{
    public function __construct(private TransactionalDataReset $reset)
    {
        $this->middleware(['auth', 'is_admin']);
    }

    /** Confirmation screen with the dry-run preview (nothing is changed here). */
    public function index()
    {
        $this->authorize('resetData', Settings::class);

        return view('dataReset', [
            'preview' => $this->reset->preview(),
            'phrase' => DataResetRequest::PHRASE,
            'result' => session('reset_result'),
        ]);
    }

    public function run(DataResetRequest $request)
    {
        $this->authorize('resetData', Settings::class);
        $data = $request->validated();

        try {
            $result = $this->reset->run((bool) ($data['reset_opening_balances'] ?? false), (int) auth()->id());
        } catch (\Throwable $e) {
            Log::error('Transactional data reset failed: '.$e->getMessage(), ['user_id' => auth()->id(), 'exception' => $e]);

            return redirect()->route('data-reset.index')
                ->with('fails', 'The reset failed and was rolled back. Nothing was deleted. See the application log for details.');
        }

        return redirect()->route('data-reset.index')
            ->with('reset_result', $result)
            ->with('success', 'Test / transactional data reset: '.$result['total_deleted'].' records deleted.');
    }
}
