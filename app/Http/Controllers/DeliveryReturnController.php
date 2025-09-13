<?php

namespace App\Http\Controllers;

use App\Models\DeliveryReturn;
use Illuminate\Http\Request;
use App\Http\Requests\DeliveryReturnRequest;
use App\Repositories\DeliveryRepository;
use App\Repositories\StockItemRepository;
use App\Repositories\StockRepository;
use App\Repositories\OrderRepository;
use App\Repositories\DeliveryReturnRepository;
use App\Repositories\DeliveryReturnItemRepository;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryReturnController extends Controller
{
    protected $deliveryRepository;
    protected $stockItemRepository;
    protected $stockRepository;
    protected $orderRepository;
    protected $deliveryReturnRepository;
    protected $deliveryReturnItemRepository;

    public function __construct(
        DeliveryRepository $deliveryRepository,
        StockItemRepository $stockItemRepository,
        StockRepository $stockRepository,
        OrderRepository $orderRepository,
        DeliveryReturnRepository $deliveryReturnRepository,
        DeliveryReturnItemRepository $deliveryReturnItemRepository
    ) {
        $this->middleware(['auth']);
        $this->deliveryRepository = $deliveryRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockRepository = $stockRepository;
        $this->orderRepository = $orderRepository;
        $this->deliveryReturnRepository = $deliveryReturnRepository;
        $this->deliveryReturnItemRepository = $deliveryReturnItemRepository;
    }

    public function index()
    {
        $returns = $this->deliveryReturnRepository->all();

        return view('deliveryReturn', [
            'returns' => $returns,
        ]);
    }

    public function create($deliveryId)
    {
        // Get delivery information
        $delivery = $this->deliveryRepository->get($deliveryId);
        
        if (!$delivery) {
            return redirect()->back()->with(['fails' => 'Delivery not found']);
        }

        // Get delivered items for this delivery
        $deliveredItems = $this->stockItemRepository->delivery($deliveryId);
        
        // Get already returned items for this delivery
        $returnedItems = $this->deliveryReturnItemRepository->getByDelivery($deliveryId);
        
        // Calculate remaining returnable quantities
        $returnableItems = $deliveredItems->map(function ($item) use ($returnedItems) {
            $returnedItem = $returnedItems->firstWhere('stock_item_id', $item->stock_item_id);
            $returnedQty = $returnedItem ? $returnedItem->quantity : 0;
            $item->returned_qty = $returnedQty;
            $item->returnable_qty = $item->quantity - $returnedQty;
            return $item;
        })->filter(function ($item) {
            return $item->returnable_qty > 0;
        });

        // Generate return number
        $returnNo = $this->generateReturnNo($deliveryId);

        return view('addDeliveryReturn', [
            'delivery' => $delivery,
            'returnableItems' => $returnableItems,
            'returnNo' => $returnNo,
        ]);
    }

    public function store(DeliveryReturnRequest $request)
    {
        $validatedData = $request->validated();
        
        if (array_sum($request->input('return_quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Please specify quantities to return'])->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Store delivery return
            $returnId = $this->deliveryReturnRepository->store($validatedData);
            
            // Store return items and adjust stock
            $stockItemIds = $request->input('stock_item_id');
            $returnQuantities = $request->input('return_quantity');
            $reasons = $request->input('reason');
            
            $this->storeReturnItems($returnId, $stockItemIds, $returnQuantities, $reasons);
            
            // Create stock adjustment entries
            $this->adjustStock($validatedData['delivery_id'], $stockItemIds, $returnQuantities);
            
            DB::commit();
            
            return redirect()->route('delivery-return.show', $returnId)->with('success', 'Return processed successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fails' => 'Error processing return: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $return = $this->deliveryReturnRepository->get($id);
        $returnItems = $this->deliveryReturnItemRepository->get($id);

        return view('deliveryReturnInfo', [
            'return' => $return,
            'returnItems' => $returnItems,
        ]);
    }

    public function edit($id)
    {
        $return = $this->deliveryReturnRepository->get($id);
        $returnItems = $this->deliveryReturnItemRepository->get($id);
        $delivery = $this->deliveryRepository->get($return->delivery_id);
        $deliveredItems = $this->stockItemRepository->delivery($return->delivery_id);

        return view('editDeliveryReturn', [
            'return' => $return,
            'returnItems' => $returnItems,
            'delivery' => $delivery,
            'deliveredItems' => $deliveredItems,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (array_sum($request->input('return_quantity', [])) == 0) {
            return redirect()->back()->with(['fails' => 'Please specify quantities to return'])->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Update delivery return
            $this->deliveryReturnRepository->update($id, $request->input());
            
            // Update return items
            $this->deliveryReturnItemRepository->update($id, $request->input());
            
            DB::commit();
            
            return redirect()->route('delivery-return.show', $id)->with('success', 'Return updated successfully');
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['fails' => 'Error updating return: ' . $e->getMessage()])->withInput();
        }
    }

    private function storeReturnItems($returnId, $stockItemIds, $returnQuantities, $reasons)
    {
        foreach ($returnQuantities as $key => $quantity) {
            if ($quantity > 0) {
                $stockItemId = $stockItemIds[$key] ?? null;
                $reason = $reasons[$key] ?? null;
                
                $returnItemData = [
                    'delivery_return_id' => $returnId,
                    'stock_item_id' => $stockItemId,
                    'quantity' => $quantity,
                    'reason' => $reason,
                ];
                
                $this->deliveryReturnItemRepository->store($returnItemData);
            }
        }
    }

    private function adjustStock($deliveryId, $stockItemIds, $returnQuantities)
    {
        // Get the original delivery stock record
        $deliveryStock = DB::table('stocks')
            ->join('deliveries', 'deliveries.stock_id', '=', 'stocks.stock_id')
            ->where('deliveries.delivery_id', $deliveryId)
            ->select('stocks.*')
            ->first();

        if (!$deliveryStock) {
            throw new \Exception('Original delivery stock record not found');
        }

        // Create a new stock entry for the return (stock in)
        $returnStockData = [
            'stock_no' => 'RET-' . $deliveryStock->stock_no,
            'order_id' => $deliveryStock->order_id,
            'table_name' => 'delivery_returns',
            'employee_id' => $deliveryStock->employee_id,
            'stock_type' => 1, // Stock In
            'stock_date' => Carbon::now()->format('Y-m-d'),
            'stock_status' => 1, // Completely Received
            'description' => 'Return from delivery: ' . $deliveryStock->stock_no,
        ];

        $returnStockId = $this->stockRepository->store($returnStockData);

        // Create stock items for returned quantities
        foreach ($returnQuantities as $key => $quantity) {
            if ($quantity > 0) {
                $stockItemId = $stockItemIds[$key] ?? null;
                
                // Get original stock item details
                $originalStockItem = DB::table('stock_items')->where('stock_item_id', $stockItemId)->first();
                
                if ($originalStockItem) {
                    $returnStockItemData = [
                        'stock_id' => $returnStockId,
                        'product_type_id' => $originalStockItem->product_type_id,
                        'material_id' => $originalStockItem->material_id,
                        'quantity' => $quantity,
                        'stage_id' => $originalStockItem->stage_id,
                        'work_logs' => '0',
                        'work_wages' => '0',
                    ];
                    
                    $this->stockItemRepository->store($returnStockItemData);
                }
            }
        }
    }

    private function generateReturnNo($deliveryId)
    {
        $yearMonth = Carbon::now()->format('ym');
        $count = DB::table('delivery_returns')->where('delivery_id', $deliveryId)->count();
        
        return 'DR-' . $yearMonth . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }
}
