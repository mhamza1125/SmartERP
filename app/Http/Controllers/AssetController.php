<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index()
    {
        $this->authorize('access', Asset::class);

        // Get asset summary with net values
        $assets = Asset::getAssetSummary();

        return view('asset', [
            'assets' => $assets,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Asset::class);

        return view('addAsset');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Asset::class);

        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'transaction_type' => 'required|in:purchase,sale,depreciation,writeoff,adjustment',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('assets', 'public');
        }

        // Determine debit or credit based on transaction type
        $debit = null;
        $credit = null;

        if (in_array($validated['transaction_type'], ['purchase', 'adjustment'])) {
            // Purchase and positive adjustments increase asset value (debit)
            $debit = $validated['amount'];
        } else {
            // Sale, depreciation, writeoff decrease asset value (credit)
            $credit = $validated['amount'];
        }

        Asset::create([
            'asset_name' => $validated['asset_name'],
            'transaction_type' => $validated['transaction_type'],
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
            'debit' => $debit,
            'credit' => $credit,
            'attachment' => $attachment,
        ]);

        return redirect()->route('asset')->with('success', 'Asset transaction recorded successfully');
    }

    public function show($assetName)
    {
        $this->authorize('show', Asset::class);

        // Get all transactions for this asset
        $transactions = Asset::getAssetLedger($assetName);

        if ($transactions->isEmpty()) {
            abort(404, 'Asset not found');
        }

        // Calculate net asset value
        $netValue = Asset::calculateAssetValue($assetName);

        return view('assetInfo', [
            'assetName' => $assetName,
            'transactions' => $transactions,
            'netValue' => $netValue,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('edit', Asset::class);
        $asset = Asset::findOrFail($id);

        return view('editAsset', [
            'asset' => $asset,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit', Asset::class);
        $asset = Asset::findOrFail($id);

        $validated = $request->validate([
            'asset_name' => 'required|string|max:255',
            'transaction_type' => 'required|in:purchase,sale,depreciation,writeoff,adjustment',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            if ($asset->attachment) {
                Storage::disk('public')->delete($asset->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('assets', 'public');
        }

        // Determine debit or credit based on transaction type
        $debit = null;
        $credit = null;

        if (in_array($validated['transaction_type'], ['purchase', 'adjustment'])) {
            // Purchase and positive adjustments increase asset value (debit)
            $debit = $validated['amount'];
        } else {
            // Sale, depreciation, writeoff decrease asset value (credit)
            $credit = $validated['amount'];
        }

        $asset->update([
            'asset_name' => $validated['asset_name'],
            'transaction_type' => $validated['transaction_type'],
            'description' => $validated['description'],
            'transaction_date' => $validated['transaction_date'],
            'debit' => $debit,
            'credit' => $credit,
            'attachment' => $validated['attachment'] ?? $asset->attachment,
        ]);

        return redirect()->route('asset.show', $asset->asset_name)->with('success', 'Asset transaction updated successfully');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Asset::class);
        $asset = Asset::findOrFail($id);

        if ($asset->attachment) {
            Storage::disk('public')->delete($asset->attachment);
        }

        $asset->delete();

        return redirect()->route('asset')->with('success', 'Asset transaction deleted successfully');
    }

    /**
     * Add a new transaction to an existing asset
     */
    public function addTransaction($assetName)
    {
        $this->authorize('create', Asset::class);

        return view('addAssetTransaction', [
            'assetName' => $assetName,
        ]);
    }
}

