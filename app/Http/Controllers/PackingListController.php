<?php

namespace App\Http\Controllers;

use App\Models\PackingList;
use App\Repositories\PackingListRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\StockItemRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingListController extends Controller
{
    protected $packingListRepository;
    protected $deliveryRepository;
    protected $stockItemRepository;

    public function __construct(
        PackingListRepository $packingListRepository,
        DeliveryRepository $deliveryRepository,
        StockItemRepository $stockItemRepository
    ) {
        $this->middleware(['auth', 'all']);
        $this->packingListRepository = $packingListRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->stockItemRepository = $stockItemRepository;
    }

    public function index()
    {
        $packingLists = $this->packingListRepository->all();

        return view('packingList', [
            'packingLists' => $packingLists,
        ]);
    }

    public function create($deliveryId)
    {
        // Check if packing list already exists for this delivery
        $existingPackingList = $this->packingListRepository->getByDelivery($deliveryId);
        if ($existingPackingList) {
            return redirect()->route('packingList.show', $existingPackingList->packing_list_id)
                ->with('fails', 'Packing List already exists for this delivery');
        }

        // Get delivery details
        $delivery = $this->deliveryRepository->get($deliveryId);
        if (!$delivery) {
            return redirect()->route('delivery')->with('fails', 'Delivery not found');
        }

        // Get delivery items (products)
        $deliveryItems = $this->stockItemRepository->delivery($deliveryId);

        return view('addPackingList', [
            'delivery' => $delivery,
            'deliveryItems' => $deliveryItems,
            'deliveryId' => $deliveryId,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'delivery_id' => 'required|exists:deliveries,delivery_id',
            'groups' => 'required|array|min:1',
            'groups.*.carton_from' => 'required|integer|min:1',
            'groups.*.carton_to' => 'required|integer|min:1',
            'groups.*.products' => 'required|array|min:1',
            'groups.*.products.*.product_id' => 'required|exists:products,product_id',
            'groups.*.products.*.pcs_each_carton' => 'required|integer|min:1',
            'groups.*.products.*.total_pcs' => 'required|integer|min:1',
        ]);

        // Validate carton_to >= carton_from
        foreach ($validatedData['groups'] as $index => $group) {
            if ($group['carton_to'] < $group['carton_from']) {
                return redirect()->back()
                    ->with('fails', "Group ".($index + 1).": Carton To must be greater than or equal to Carton From")
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Get delivery to find order_id
            $delivery = $this->deliveryRepository->get($validatedData['delivery_id']);

            // Create packing list
            $packingListId = $this->packingListRepository->store([
                'delivery_id' => $validatedData['delivery_id'],
                'order_id' => $delivery->order_id,
            ]);

            // Create carton groups and items
            foreach ($validatedData['groups'] as $group) {
                $cartonId = $this->packingListRepository->storeCarton([
                    'packing_list_id' => $packingListId,
                    'carton_from' => $group['carton_from'],
                    'carton_to' => $group['carton_to'],
                ]);

                foreach ($group['products'] as $product) {
                    $this->packingListRepository->storeCartonItem([
                        'packing_carton_id' => $cartonId,
                        'product_id' => $product['product_id'],
                        'pcs_each_carton' => $product['pcs_each_carton'],
                        'total_pcs' => $product['total_pcs'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('packingList.show', $packingListId)
                ->with('success', 'Packing List created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('fails', 'Error creating packing list: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $packingList = $this->packingListRepository->get($id);
        if (!$packingList) {
            return redirect()->route('delivery')->with('fails', 'Packing List not found');
        }

        $cartons = $this->packingListRepository->getCartons($id);

        // Get items for each carton
        foreach ($cartons as $carton) {
            $carton->items = $this->packingListRepository->getCartonItems($carton->packing_carton_id);
        }

        return view('packingListInfo', [
            'packingList' => $packingList,
            'cartons' => $cartons,
        ]);
    }

    public function edit($id)
    {
        $packingList = $this->packingListRepository->get($id);
        if (!$packingList) {
            return redirect()->route('delivery')->with('fails', 'Packing List not found');
        }

        // Get delivery details
        $delivery = $this->deliveryRepository->get($packingList->delivery_id);

        // Get all delivery items
        $deliveryItems = $this->stockItemRepository->delivery($packingList->delivery_id);

        // Get existing cartons and their items
        $cartons = $this->packingListRepository->getCartons($id);
        foreach ($cartons as $carton) {
            $carton->items = $this->packingListRepository->getCartonItems($carton->packing_carton_id);
        }

        return view('editPackingList', [
            'packingList' => $packingList,
            'delivery' => $delivery,
            'deliveryItems' => $deliveryItems,
            'cartons' => $cartons,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'groups' => 'required|array|min:1',
            'groups.*.carton_from' => 'required|integer|min:1',
            'groups.*.carton_to' => 'required|integer|min:1',
            'groups.*.products' => 'required|array|min:1',
            'groups.*.products.*.product_id' => 'required|exists:products,product_id',
            'groups.*.products.*.pcs_each_carton' => 'required|integer|min:1',
            'groups.*.products.*.total_pcs' => 'required|integer|min:1',
        ]);

        // Validate carton_to >= carton_from
        foreach ($validatedData['groups'] as $index => $group) {
            if ($group['carton_to'] < $group['carton_from']) {
                return redirect()->back()
                    ->with('fails', "Group ".($index + 1).": Carton To must be greater than or equal to Carton From")
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Delete existing cartons and items
            $this->packingListRepository->deleteCartons($id);

            // Create new carton groups and items
            foreach ($validatedData['groups'] as $group) {
                $cartonId = $this->packingListRepository->storeCarton([
                    'packing_list_id' => $id,
                    'carton_from' => $group['carton_from'],
                    'carton_to' => $group['carton_to'],
                ]);

                foreach ($group['products'] as $product) {
                    $this->packingListRepository->storeCartonItem([
                        'packing_carton_id' => $cartonId,
                        'product_id' => $product['product_id'],
                        'pcs_each_carton' => $product['pcs_each_carton'],
                        'total_pcs' => $product['total_pcs'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('packingList.show', $id)
                ->with('success', 'Packing List updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('fails', 'Error updating packing list: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function printPackingList($orderId)
    {
        // Get packing list by order_id
        $packingList = $this->packingListRepository->getByOrder($orderId);
        if (!$packingList) {
            return redirect()->route('order')->with('fails', 'Packing List not found for this order');
        }

        // Get cartons and their items
        $cartons = $this->packingListRepository->getCartons($packingList->packing_list_id);
        foreach ($cartons as $carton) {
            $carton->items = $this->packingListRepository->getCartonItems($carton->packing_carton_id);
        }

        return view('print.packing-list', [
            'packingList' => $packingList,
            'cartons' => $cartons,
        ]);
    }
}

