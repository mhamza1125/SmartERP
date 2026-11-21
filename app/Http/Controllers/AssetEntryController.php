<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetRequest;
use App\Repositories\AssetRepository;
use App\Repositories\BankRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Shared screens of the asset and liability registers (AssetController / LiabilityController):
 * list of names with their value, ledger of one name, add / edit / delete an entry.
 */
abstract class AssetEntryController extends Controller
{
    public function __construct(
        protected AssetRepository $repository,
        protected BankRepository $bankRepository,
    ) {
        $this->middleware(['auth', 'all']);
    }

    /** Labels, route prefix and policy model of the register, for the shared views. */
    abstract protected function register(): array;

    public function index()
    {
        $this->authorize('access', $this->register()['model']);

        return view('asset', [
            'register' => $this->register(),
            'assets' => $this->repository->all(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', $this->register()['model']);

        return view('addAsset', $this->formData() + [
            'entry' => null,
            'presetName' => (string) $request->query('name', ''),
        ]);
    }

    public function store(AssetRequest $request)
    {
        $this->authorize('create', $this->register()['model']);
        $data = $request->validated();
        $data['attachment'] = $this->storeAttachment($request);

        try {
            $this->repository->store($data);
        } catch (ValidationException $e) {
            $this->discardAttachment($data['attachment']);
            throw $e;
        } catch (\Exception $e) {
            $this->discardAttachment($data['attachment']);
            Log::error($this->register()['title'].' entry could not be saved: '.$e->getMessage());

            return back()->withInput()->with('fails', 'The entry could not be saved. Nothing was changed.');
        }

        return redirect()->route($this->register()['route'].'.show', ['name' => trim($data['asset_name'])])
            ->with('success', $this->register()['title'].' entry saved successfully');
    }

    public function show(Request $request)
    {
        $this->authorize('show', $this->register()['model']);
        $name = (string) $request->query('name', '');
        $entries = $this->repository->ledger($name);
        abort_if($entries->isEmpty(), 404, $this->register()['title'].' not found');

        return view('assetInfo', [
            'register' => $this->register(),
            'assetName' => $name,
            'transactions' => $entries,
            'netValue' => $this->repository->balance($name),
            'types' => $this->repository->types(),
        ]);
    }

    /** View button of the entry's cash / bank transaction (Transactions, Cash Book, Bank Ledger). */
    public function entry($transactionId)
    {
        $this->authorize('show', $this->register()['model']);
        $entry = $this->repository->byTransaction((int) $transactionId);
        abort_unless($entry, 404);

        return redirect()->route($this->register()['route'].'.show', ['name' => $entry->asset_name]);
    }

    public function edit($id)
    {
        $this->authorize('edit', $this->register()['model']);

        return view('editAsset', $this->formData() + [
            'entry' => $this->repository->get($id),
            'presetName' => '',
        ]);
    }

    public function update(AssetRequest $request, $id)
    {
        $this->authorize('edit', $this->register()['model']);
        $data = $request->validated();
        $data['attachment'] = $this->storeAttachment($request);

        try {
            $this->repository->update($id, $data);
        } catch (ValidationException $e) {
            $this->discardAttachment($data['attachment']);
            throw $e;
        } catch (\Exception $e) {
            $this->discardAttachment($data['attachment']);
            Log::error($this->register()['title'].' entry '.$id.' could not be updated: '.$e->getMessage());

            return back()->withInput()->with('fails', 'The entry could not be updated. Nothing was changed.');
        }

        return redirect()->route($this->register()['route'].'.show', ['name' => trim($data['asset_name'])])
            ->with('success', $this->register()['title'].' entry updated successfully');
    }

    public function deleteCheck($id)
    {
        $this->authorize('delete', $this->register()['model']);

        return $this->deletionCheckResponse($this->repository->deletionImpact($id));
    }

    public function destroy($id)
    {
        $this->authorize('delete', $this->register()['model']);
        $name = $this->repository->get($id)->asset_name;
        $remaining = $this->repository->ledger($name)->count() > 1;

        return $this->runDeletion(
            $this->repository->deletionImpact($id),
            fn () => $this->repository->delete($id),
            $remaining
                ? route($this->register()['route'].'.show', ['name' => $name])
                : route($this->register()['route'])
        );
    }

    private function formData(): array
    {
        return [
            'register' => $this->register(),
            'types' => $this->repository->types(),
            'names' => $this->repository->names(),
            'banks' => $this->bankRepository->self(),
        ];
    }

    private function storeAttachment(Request $request): ?string
    {
        return $request->hasFile('attachment') ? $request->file('attachment')->store('assets', 'public') : null;
    }

    private function discardAttachment(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
