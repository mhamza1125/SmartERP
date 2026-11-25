<?php

namespace App\Http\Controllers;

use App\Exceptions\DocumentLockedException;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\HeadRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\VendorRepository;
use App\Repositories\ProductRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PartyPriceRepository;
use App\Repositories\ReturnMaterialRepository;
use App\Repositories\ReceiveMaterialRepository;

class PurchaseController extends Controller
{
    protected $orderRepository;
    
    protected $headRepository;

    protected $vendorRepository;

    protected $purchaseRepository;

    protected $materialRepository;

    protected $productRepository;

    protected $transactionRepository;

    protected $purchaseItemRepository;

    protected $returnMaterialRepository;

    protected $receiveMaterialRepository;

    protected $orderItemRepository;

    public function __construct(
        ProductRepository $productRepository,
        OrderRepository $orderRepository,
        HeadRepository $headRepository,
        VendorRepository $vendorRepository,
        PurchaseRepository $purchaseRepository,
        MaterialRepository $materialRepository,
        TransactionRepository $transactionRepository,
        PurchaseItemRepository $purchaseItemRepository,
        ReturnMaterialRepository $returnMaterialRepository,
        ReceiveMaterialRepository $receiveMaterialRepository,
        OrderItemRepository $orderItemRepository,
    ) {
        $this->middleware(['auth', 'all']);
        $this->productRepository = $productRepository;
        $this->headRepository = $headRepository;
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->purchaseRepository = $purchaseRepository;
        $this->materialRepository = $materialRepository;
        $this->transactionRepository = $transactionRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->returnMaterialRepository = $returnMaterialRepository;
        $this->receiveMaterialRepository = $receiveMaterialRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function index()
    {
        $this->authorize('access', Purchase::class);
        $purchase = $this->purchaseRepository->all();

        return view('purchase', [
            'purchase' => $purchase,
        ]);
    }

    public function ajaxPMQty(Request $request)
    {
        $this->authorizeAny([['create', Purchase::class], ['edit', Purchase::class]]);
        // Ajax Material Qty against Order
        $orderId = $request->input('orderId');
        $materialId = $request->input('materialId');
        $estimate = $this->purchaseItemRepository->estimateMaterial($orderId, $materialId);

        return response()->json(['data' => $estimate]);
    }

    public function ajaxOrderPurchaseItems(Request $request)
    {
        // Ajax: material / product ids that may be purchased against an order
        $this->authorize('create', Purchase::class);
        $orderId = (int) $request->input('orderId');
        $ids = $request->input('type') === 'product'
            ? $this->orderItemRepository->purchasableProductTypeIds($orderId)
            : $this->orderItemRepository->purchasableMaterialIds($orderId);

        return response()->json(['success' => true, 'message' => '', 'data' => $ids]);
    }

    // This is for Material Purchase
    public function create()
    {
        $this->authorize('create', Purchase::class);
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->vendor();
        $material = $this->materialRepository->all();
        $count = $this->purchaseRepository->refNo();

        return view('addPurchase', [
            'count' => $count,
            'order' => $order,
            'vendor' => $vendor,
            'material' => $material,
        ]);
    }

    // This is for Product Purchase
    public function create2()
    {
        $this->authorize('create', Purchase::class);
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->vendor();
        $count = $this->purchaseRepository->refNo();
        $product = $this->productRepository->activeTypes();
        $head = $this->headRepository->get('16');

        return view('addProductPurchase', [
            'count' => $count,
            'order' => $order,
            'vendor' => $vendor,
            'product' => $product,
            'head' => $head,
            // 'material' => $material,
        ]);
    }

    public function store(PurchaseRequest $request, PartyPriceRepository $partyPriceRepository)
    {
        $this->authorize('create', Purchase::class);
        $validatedData = $request->validated();
        if (! $request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $materials = $request->input('material_id');
        $products = $request->input('product_type_id');
        $stages = $request->input('product_stage_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');
        // Header and lines are saved together or not at all
        $getId = DB::transaction(function () use ($validatedData, $materials, $products, $stages, $prices, $quantities) {
            $getId = $this->purchaseRepository->store(array_intersect_key($validatedData, array_flip(array_merge(self::EDITABLE_HEADER, ['purchase_no', 'purchase_type']))));
            $this->storePI($getId, $materials, $products, $stages, $prices, $quantities);

            return $getId;
        });
        $this->syncVendorPrices($partyPriceRepository, $getId);

        return redirect()->route('purchase.show', $getId)->with('success', 'Record Inserted Successfully');
    }

    public function show($id)
    {
        $this->authorize('show', Purchase::class);
        $purchase = $this->purchaseRepository->get($id);
        $receiveTimes = $this->receiveMaterialRepository->times($id);
        $returnTimes = $this->returnMaterialRepository->times($id);
        $transaction = $this->transactionRepository->getPPayment($id);
        if($purchase['purchase_type'] == 'material'){   
            $purchaseItem = $this->purchaseItemRepository->get($id);
            $receiveSum = $this->receiveMaterialRepository->rSum($id);
            $receiveAll = $this->receiveMaterialRepository->rAll($id);
            $returnAll = $this->returnMaterialRepository->rAll($id);
        } else {
            $purchaseItem = $this->purchaseItemRepository->get2($id);
            $receiveSum = $this->receiveMaterialRepository->rSum2($id);
            $receiveAll = $this->receiveMaterialRepository->rAll2($id);
            $returnAll = $this->returnMaterialRepository->rAll2($id);
        }

        return view('purchaseInfo', [
            'purchase' => $purchase,
            'transaction' => $transaction,
            'purchaseItem' => $purchaseItem,
            'receiveSum' => $receiveSum,
            'receiveAll' => $receiveAll,
            'receiveTimes' => $receiveTimes,
            'count' => $receiveTimes->count(),
            'returnAll' => $returnAll,
            'returnTimes' => $returnTimes,
            'count2' => $returnTimes->count(),
        ]);
    }

    /**
     * Print purchase information
     */
    public function printPurchase($id)
    {
        $this->authorize('show', Purchase::class);
        $purchase = $this->purchaseRepository->get($id);
        $receiveTimes = $this->receiveMaterialRepository->times($id);
        $returnTimes = $this->returnMaterialRepository->times($id);
        $transaction = $this->transactionRepository->getPPayment($id);
        if($purchase['purchase_type'] == 'material'){
            $purchaseItem = $this->purchaseItemRepository->get($id);
            $receiveSum = $this->receiveMaterialRepository->rSum($id);
            $receiveAll = $this->receiveMaterialRepository->rAll($id);
            $returnAll = $this->returnMaterialRepository->rAll($id);
        } else {
            $purchaseItem = $this->purchaseItemRepository->get2($id);
            $receiveSum = $this->receiveMaterialRepository->rSum2($id);
            $receiveAll = $this->receiveMaterialRepository->rAll2($id);
            $returnAll = $this->returnMaterialRepository->rAll2($id);
        }

        return view('print.purchase', [
            'purchase' => $purchase,
            'transaction' => $transaction,
            'purchaseItem' => $purchaseItem,
            'receiveSum' => $receiveSum,
            'receiveAll' => $receiveAll,
            'receiveTimes' => $receiveTimes,
            'count' => $receiveTimes->count(),
            'returnAll' => $returnAll,
            'returnTimes' => $returnTimes,
            'count2' => $returnTimes->count(),
        ]);
    }

    // This is for Material Purchase
    public function edit(Purchase $id)
    {
        $this->authorize('edit', Purchase::class);
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->vendor();
        $material = $this->materialRepository->all();
        $purchaseItem = $this->purchaseItemRepository->get($id->purchase_id);

        return view('editPurchase', [
            'purchase' => $id,
            'order' => $order,
            'vendor' => $vendor,
            'material' => $material,
            'purchaseItem' => $purchaseItem,
        ]);
    }

    // This is for Product Purchase
    public function edit2(Purchase $id)
    {
        $this->authorize('edit', Purchase::class);
        $order = $this->orderRepository->active();
        $vendor = $this->vendorRepository->vendor();
        $product = $this->productRepository->activeTypes();
        $head = $this->headRepository->get('16');
        $purchaseItem = $this->purchaseItemRepository->get2($id->purchase_id);

        return view('editProductPurchase', [
            'purchase' => $id,
            'order' => $order,
            'vendor' => $vendor,
            'purchaseItem' => $purchaseItem,
            'product' => $product,
            'head' => $head,
        ]);
    }

    /** Purchase header columns a save may set (purchase_no / purchase_type only on create). */
    private const EDITABLE_HEADER = ['vendor_id', 'order_id', 'description', 'purchase_date', 'require_date'];

    public function update(PurchaseRequest $request, $id, PartyPriceRepository $partyPriceRepository)
    {
        $this->authorize('edit', Purchase::class);
        if (! $request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $validated = $request->validated();
        $lines = array_intersect_key($validated, array_flip(['material_id', 'product_type_id', 'product_stage_id', 'quantity', 'price']));
        $before = $partyPriceRepository->snapshot('purchase', (int) $id);
        try {
            DB::transaction(function () use ($id, $validated, $lines) {
                // Lines locked first; received lines keep item, price and at least the received quantity (D4)
                DB::table('purchase_items')->where('purchase_id', $id)->orderBy('purchase_item_id')->lockForUpdate()->pluck('purchase_item_id');
                $this->purchaseItemRepository->assertReceivedLinesKept((int) $id, $lines);
                $this->purchaseRepository->update($id, array_intersect_key($validated, array_flip(self::EDITABLE_HEADER)));
                $this->purchaseItemRepository->update($id, $lines);
            });
        } catch (DocumentLockedException $e) {
            return redirect()->back()->with(['fails' => $e->getMessage()])->withInput();
        }
        $this->syncVendorPrices($partyPriceRepository, (int) $id, $before);

        return redirect()->route('purchase.show', $id)->with('success', 'Record Updated Successfully');
    }


    private function storePI($getId, $materials, $products, $stages, $prices, $quantities)
    {
        foreach ($prices as $key => $price) {
            $material = $materials[$key] ?? null;
            $product = $products[$key] ?? null;
            $stage = $stages[$key] ?? null;
            $quantity = $quantities[$key] ?? null;
            $total = $price * $quantity;
            $purchaseItem = [
                'purchase_id' => $getId,
                'product_type_id' => $product,
                'product_stage_id' => $stage,
                'material_id' => $material,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $total,
            ];
            $this->purchaseItemRepository->store($purchaseItem);
            if (!$stage) $this->materialRepository->update($material, ['cprice' => $price]);
        }
    }

    /**
     * The vendor's price of each item follows the purchase (see PartyPriceRepository). A failure here
     * must not undo the saved purchase, so it is only logged.
     */
    private function syncVendorPrices(PartyPriceRepository $partyPriceRepository, int $purchaseId, ?array $before = null): void
    {
        try {
            $partyPriceRepository->syncPurchase($purchaseId, $before);
        } catch (\Exception $e) {
            \Log::error('Vendor price update failed: '.$e->getMessage(), ['purchase_id' => $purchaseId]);
        }
    }

    /**
     * What deleting this record would affect (JSON for the delete button's confirmation).
     */
    public function deleteCheck($id)
    {
        $this->authorize('delete', Purchase::class);

        return $this->deletionCheckResponse($this->purchaseRepository->deletionImpact($id));
    }

    /**
     * Deletes the record (refused while other records depend on it; see deletionImpact()).
     */
    public function destroy($id)
    {
        $this->authorize('delete', Purchase::class);

        return $this->runDeletion(
            $this->purchaseRepository->deletionImpact($id),
            fn () => $this->purchaseRepository->delete($id),
            route('purchase')
        );
    }
}
