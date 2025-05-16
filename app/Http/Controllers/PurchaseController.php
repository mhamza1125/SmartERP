<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Repositories\HeadRepository;
use App\Repositories\OrderRepository;
use App\Http\Requests\PurchaseRequest;
use App\Repositories\VendorRepository;
use App\Repositories\ProductRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\PurchaseItemRepository;
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
        // Ajax Material Qty against Order
        $orderId = $request->input('orderId');
        $materialId = $request->input('materialId');
        $estimate = $this->purchaseItemRepository->estimateMaterial($orderId, $materialId);

        return response()->json(['data' => $estimate]);
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

    public function store(PurchaseRequest $request)
    {
        $validatedData = $request->validated();
        if (! $request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $materials = $request->input('material_id');
        $products = $request->input('product_type_id');
        $stages = $request->input('product_stage_id');
        $prices = $request->input('price');
        $quantities = $request->input('quantity');
        $getId = $this->purchaseRepository->store($validatedData);
        $this->storePI($getId, $materials, $products, $stages, $prices, $quantities);

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

    public function update(Request $request, $id)
    {
        if (! $request->has('total')) {
            return redirect()->back()->with(['fails' => 'Fill the form properly'])->withInput();
        }
        $this->purchaseRepository->update($id, $request->input());
        $this->purchaseItemRepository->update($id, $request->input());

        return redirect()->route('purchase.show', $id)->with('success', 'Record Updated Successfully');
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorize('delete', Purchase::class);
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
}
