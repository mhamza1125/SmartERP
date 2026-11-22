<?php

namespace App\Http\Controllers;

use App\Http\Requests\PartyPriceRequest;
use App\Models\Order;
use App\Models\PartyPrice;
use App\Models\Purchase;
use App\Repositories\PartyPriceRepository;
use App\Support\DeletionImpact;

/**
 * Vendor (buying) and customer (selling) prices of materials / products.
 * Vendor prices: purchase creators / editors; customer prices: order creators / editors.
 */
class PartyPriceController extends Controller
{
    public function __construct(protected PartyPriceRepository $partyPriceRepository)
    {
        $this->middleware(['auth', 'all']);
    }

    /** JSON for the read-only "Party Price" field on the purchase / order forms. */
    public function lookup(PartyPriceRequest $request)
    {
        $data = $request->validated();
        $this->authorizeParty($data['party_type']);

        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $this->partyPriceRepository->lookup(
                $data['party_type'], (int) $data['party_id'],
                $data['material_id'] ?? 0, $data['product_type_id'] ?? 0, $data['stage_id'] ?? 0
            ),
        ]);
    }

    /** Sets a price by hand (material / product page); an existing price of the item is changed. */
    public function store(PartyPriceRequest $request)
    {
        $data = $request->validated();
        $this->authorizeParty($data['party_type']);

        try {
            $this->partyPriceRepository->store($data);
        } catch (\Exception $e) {
            \Log::error('Party price save failed: '.$e->getMessage(), $data);

            return back()->with('fails', 'Could not save the price. Please try again.');
        }

        return back()->with('success', ucfirst($data['party_type']).' price saved');
    }

    public function deleteCheck($id)
    {
        $this->authorizeParty($this->partyPriceRepository->get($id)->party_type);

        return $this->deletionCheckResponse($this->impact($id));
    }

    /** Removes a price; its history stays. */
    public function destroy($id)
    {
        $this->authorizeParty($this->partyPriceRepository->get($id)->party_type);

        return $this->runDeletion($this->impact($id), fn () => $this->partyPriceRepository->delete($id), url()->previous());
    }

    private function impact($id): DeletionImpact
    {
        $row = $this->partyPriceRepository->get($id);

        return DeletionImpact::for(ucfirst($row->party_type).' price '.number_format((float) $row->price, 2))
            ->note('The price history is kept; the next purchase / order sets the price again.');
    }

    private function authorizeParty(string $partyType): void
    {
        $model = $partyType === PartyPrice::PARTY_VENDOR ? Purchase::class : Order::class;
        $this->authorizeAny([['create', $model], ['edit', $model]]);
    }
}
