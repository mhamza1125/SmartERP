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
        $this->authorize('access', PackingList::class);
        $packingLists = $this->packingListRepository->all();

        return view('packingList', [
            'packingLists' => $packingLists,
        ]);
    }

    public function create($deliveryId)
    {
        $this->authorize('create', PackingList::class);
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
        $this->authorize('create', PackingList::class);
        $validatedData = $request->validate([
            'delivery_id' => 'required|exists:deliveries,delivery_id',
            'pallet_qty' => 'nullable|integer|min:1',
            'pallet_weight' => 'nullable|numeric|min:0',
            'pallet_dimension' => 'nullable|string',
            'groups' => 'required|array|min:1',
            'groups.*.carton_from' => 'required|integer|min:1',
            'groups.*.carton_to' => 'required|integer|min:1',
            'groups.*.box_dimension' => 'nullable|string',
            'groups.*.box_weight' => 'nullable|numeric|min:0',
            'groups.*.products' => 'required|array|min:1',
            'groups.*.products.*.product_id' => 'required|exists:products,product_id',
            'groups.*.products.*.pcs_each_carton' => 'required|integer|min:1',
            'groups.*.products.*.total_pcs' => 'required|integer|min:1',
            'groups.*.products.*.instance_id' => 'nullable|string',
        ]);

        // Validate carton_to >= carton_from
        foreach ($validatedData['groups'] as $index => $group) {
            if ($group['carton_to'] < $group['carton_from']) {
                return redirect()->back()
                    ->with('fails', "Group ".($index + 1).": Carton To must be greater than or equal to Carton From")
                    ->withInput();
            }
        }

        // Validate split products - total allocated shouldn't exceed delivered quantity
        $productAllocations = [];
        foreach ($validatedData['groups'] as $group) {
            foreach ($group['products'] as $product) {
                $productId = $product['product_id'];
                $pcsEachCarton = $product['pcs_each_carton'];
                $totalPcs = $product['total_pcs'];

                if (!isset($productAllocations[$productId])) {
                    $productAllocations[$productId] = [
                        'total_delivered' => $totalPcs,
                        'total_allocated' => 0,
                    ];
                }

                $productAllocations[$productId]['total_allocated'] += $pcsEachCarton;
            }
        }

        // Check if any product exceeds its delivered quantity
        foreach ($productAllocations as $productId => $allocation) {
            if ($allocation['total_allocated'] > $allocation['total_delivered']) {
                return redirect()->back()
                    ->with('fails', "Product ID {$productId}: Total allocated ({$allocation['total_allocated']}) exceeds delivered quantity ({$allocation['total_delivered']})")
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Get delivery to find order_id
            $delivery = $this->deliveryRepository->get($validatedData['delivery_id']);

            // Create packing list with pallet information
            $packingListData = [
                'delivery_id' => $validatedData['delivery_id'],
                'order_id' => $delivery->order_id,
            ];

            // Add pallet fields if provided
            if (isset($validatedData['pallet_qty'])) {
                $packingListData['pallet_qty'] = $validatedData['pallet_qty'];
            }
            if (isset($validatedData['pallet_weight'])) {
                $packingListData['pallet_weight'] = $validatedData['pallet_weight'];
            }
            if (isset($validatedData['pallet_dimension'])) {
                $packingListData['pallet_dimension'] = $validatedData['pallet_dimension'];
            }

            $packingListId = $this->packingListRepository->store($packingListData);

            // Create carton groups and items
            foreach ($validatedData['groups'] as $group) {
                $cartonData = [
                    'packing_list_id' => $packingListId,
                    'carton_from' => $group['carton_from'],
                    'carton_to' => $group['carton_to'],
                ];

                // Add box dimension and weight if provided
                if (isset($group['box_dimension'])) {
                    $cartonData['box_dimension'] = $group['box_dimension'];
                }
                if (isset($group['box_weight'])) {
                    $cartonData['box_weight'] = $group['box_weight'];
                }

                $cartonId = $this->packingListRepository->storeCarton($cartonData);

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
        $this->authorize('show', PackingList::class);
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
        $this->authorize('edit', PackingList::class);
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
        $this->authorize('edit', PackingList::class);
        $validatedData = $request->validate([
            'pallet_qty' => 'nullable|integer|min:1',
            'pallet_weight' => 'nullable|numeric|min:0',
            'pallet_dimension' => 'nullable|string',
            'groups' => 'required|array|min:1',
            'groups.*.carton_from' => 'required|integer|min:1',
            'groups.*.carton_to' => 'required|integer|min:1',
            'groups.*.box_dimension' => 'nullable|string',
            'groups.*.box_weight' => 'nullable|numeric|min:0',
            'groups.*.products' => 'required|array|min:1',
            'groups.*.products.*.product_id' => 'required|exists:products,product_id',
            'groups.*.products.*.pcs_each_carton' => 'required|integer|min:1',
            'groups.*.products.*.total_pcs' => 'required|integer|min:1',
            'groups.*.products.*.instance_id' => 'nullable|string',
        ]);

        // Validate carton_to >= carton_from
        foreach ($validatedData['groups'] as $index => $group) {
            if ($group['carton_to'] < $group['carton_from']) {
                return redirect()->back()
                    ->with('fails', "Group ".($index + 1).": Carton To must be greater than or equal to Carton From")
                    ->withInput();
            }
        }

        // Validate split products - total allocated shouldn't exceed delivered quantity
        $productAllocations = [];
        foreach ($validatedData['groups'] as $group) {
            foreach ($group['products'] as $product) {
                $productId = $product['product_id'];
                $pcsEachCarton = $product['pcs_each_carton'];
                $totalPcs = $product['total_pcs'];

                if (!isset($productAllocations[$productId])) {
                    $productAllocations[$productId] = [
                        'total_delivered' => $totalPcs,
                        'total_allocated' => 0,
                    ];
                }

                $productAllocations[$productId]['total_allocated'] += $pcsEachCarton;
            }
        }

        // Check if any product exceeds its delivered quantity
        foreach ($productAllocations as $productId => $allocation) {
            if ($allocation['total_allocated'] > $allocation['total_delivered']) {
                return redirect()->back()
                    ->with('fails', "Product ID {$productId}: Total allocated ({$allocation['total_allocated']}) exceeds delivered quantity ({$allocation['total_delivered']})")
                    ->withInput();
            }
        }

        try {
            DB::beginTransaction();

            // Update packing list with pallet information
            $updateData = [];
            if (isset($validatedData['pallet_qty'])) {
                $updateData['pallet_qty'] = $validatedData['pallet_qty'];
            }
            if (isset($validatedData['pallet_weight'])) {
                $updateData['pallet_weight'] = $validatedData['pallet_weight'];
            }
            if (isset($validatedData['pallet_dimension'])) {
                $updateData['pallet_dimension'] = $validatedData['pallet_dimension'];
            }

            if (!empty($updateData)) {
                $this->packingListRepository->update($id, $updateData);
            }

            // Delete existing cartons and items
            $this->packingListRepository->deleteCartons($id);

            // Create new carton groups and items
            foreach ($validatedData['groups'] as $group) {
                $cartonData = [
                    'packing_list_id' => $id,
                    'carton_from' => $group['carton_from'],
                    'carton_to' => $group['carton_to'],
                ];

                // Add box dimension and weight if provided
                if (isset($group['box_dimension'])) {
                    $cartonData['box_dimension'] = $group['box_dimension'];
                }
                if (isset($group['box_weight'])) {
                    $cartonData['box_weight'] = $group['box_weight'];
                }

                $cartonId = $this->packingListRepository->storeCarton($cartonData);

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
        $this->authorize('show', PackingList::class);
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
