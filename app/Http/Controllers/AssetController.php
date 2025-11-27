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
        $assets = Asset::all();

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
            'quantity' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $attachment = null;
        if ($request->hasFile('attachment')) {
            $attachment = $request->file('attachment')->store('assets', 'public');
        }

        Asset::create([
            'asset_name' => $validated['asset_name'],
            'quantity' => $validated['quantity'],
            'amount' => $validated['amount'],
            'attachment' => $attachment,
        ]);

        return redirect()->route('asset')->with('success', 'Asset created successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Asset::class);
        $asset = Asset::findOrFail($id);

        return view('assetInfo', [
            'asset' => $asset,
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
            'quantity' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            if ($asset->attachment) {
                Storage::disk('public')->delete($asset->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('assets', 'public');
        }

        $asset->update($validated);

        return redirect()->route('asset.show', $asset->asset_id)->with('success', 'Asset updated successfully');
    }

    public function destroy($id)
    {
        $this->authorize('delete', Asset::class);
        $asset = Asset::findOrFail($id);

        if ($asset->attachment) {
            Storage::disk('public')->delete($asset->attachment);
        }

        $asset->delete();

        return redirect()->route('asset')->with('success', 'Asset deleted successfully');
    }
}

