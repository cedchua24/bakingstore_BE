<?php

namespace App\Http\Controllers;

use App\Models\MarkUpProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarkUpProductController extends Controller
{
    /**
     * List active markup prices with optional price or profit sorting.
     */
    public function catalog(Request $request)
    {
        $validated = $request->validate([
            'sort' => 'nullable|in:highest_price,lowest_price,highest_profit,lowest_profit',
            'profit_type' => 'nullable|in:amount,margin',
        ]);

        $profitType = $validated['profit_type'] ?? 'amount';

        $query = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin(
                'branch_stock_transaction as b',
                'b.id',
                '=',
                'mup.branch_stock_transaction_id'
            )
            ->leftJoin('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select(
                'mup.id',
                'mup.product_id',
                'mup.price',
                'p.disabled',
                'mup.mark_up_option',
                'mup.profit',
                'mup.mark_up_price',
                'mup.new_price',
                'p.product_name',
                'p.quantity',
                'p.weight',
                'p.category_id',
                'p.variation',
                'p.packaging',
                'c.category_name',
                'w.warehouse_name',
                'mup.branch_stock_transaction_id',
                'mup.business_type',
                'p.sale_price'
            )
            ->selectRaw("
                CASE
                    WHEN mup.business_type = 'WHOLESALE' THEN p.stock
                    ELSE p.stock_pc
                END as stock
            ")
            ->selectRaw('(mup.new_price - mup.price) as profit_amount')
            ->selectRaw("
                CASE
                    WHEN mup.price = 0 THEN NULL
                    ELSE ROUND((mup.new_price - mup.price) / mup.price * 100, 2)
                END as profit_margin_percent
            ")
            ->where('mup.status', 1)
            ->where('p.disabled', 0);

        switch ($validated['sort'] ?? null) {
            case 'highest_price':
                $query->orderByDesc('mup.new_price');
                break;
            case 'lowest_price':
                $query->orderBy('mup.new_price');
                break;
            case 'highest_profit':
                if ($profitType === 'margin') {
                    $query->orderByRaw('CASE WHEN mup.price = 0 THEN 1 ELSE 0 END ASC');
                    $query->orderByRaw("
                        CASE
                            WHEN mup.price = 0 THEN NULL
                            ELSE (mup.new_price - mup.price) / mup.price
                        END DESC
                    ");
                } else {
                    $query->orderByRaw('(mup.new_price - mup.price) DESC');
                }
                break;
            case 'lowest_profit':
                if ($profitType === 'margin') {
                    $query->orderByRaw('CASE WHEN mup.price = 0 THEN 1 ELSE 0 END ASC');
                    $query->orderByRaw("
                        CASE
                            WHEN mup.price = 0 THEN NULL
                            ELSE (mup.new_price - mup.price) / mup.price
                        END ASC
                    ");
                } else {
                    $query->orderByRaw('(mup.new_price - mup.price) ASC');
                }
                break;
            default:
                $query->orderBy('c.ordering', 'ASC');
                break;
        }

        $data = $query
            ->orderBy('mup.id', 'DESC')
            ->get();

        return response()->json([
            'data' => $data,
            'count' => $data->count(),
            'sort' => $validated['sort'] ?? null,
            'profit_type' => $profitType,
        ]);
    }

    /**
     * List active markup products with FIFO selling availability.
     *
     * When the product cost differs from its active wholesale markup cost,
     * only stock that existed before the latest completed supplier order may
     * be sold.
     */
    public function salesAvailability()
    {
        $data = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin(
                'branch_stock_transaction as b',
                'b.id',
                '=',
                'mup.branch_stock_transaction_id'
            )
            ->leftJoin('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select(
                'mup.id',
                'mup.product_id',
                'mup.price',
                'p.price as product_price',
                'p.disabled',
                'mup.mark_up_option',
                'mup.profit',
                'mup.mark_up_price',
                'mup.new_price',
                'p.product_name',
                'p.quantity',
                'p.weight',
                'p.category_id',
                'p.variation',
                'p.packaging',
                'c.category_name',
                'w.warehouse_name',
                'mup.branch_stock_transaction_id',
                'mup.business_type',
                'p.sale_price',
                'p.stock as product_stock',
                'p.stock_pc as product_stock_pieces'
            )
            ->selectRaw("
                CASE
                    WHEN mup.business_type = 'WHOLESALE' THEN p.stock
                    ELSE p.stock_pc
                END as stock
            ")
            ->where('mup.status', 1)
            ->where('p.disabled', 0)
            ->orderBy('c.ordering', 'ASC')
            ->orderBy('mup.id', 'DESC')
            ->get();

        $productIds = $data->pluck('product_id')->unique()->values();

        $wholesaleCosts = DB::table('mark_up_product')
            ->select('product_id', 'price')
            ->where('status', 1)
            ->where('business_type', 'WHOLESALE')
            ->whereIn('product_id', $productIds)
            ->orderByDesc('id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($markups) {
                return (float) $markups->first()->price;
            });

        $latestReceivedOrders = DB::table('order_supplier as os')
            ->join(
                'order_supplier_transaction as ost',
                'ost.id',
                '=',
                'os.order_supplier_transaction_id'
            )
            ->join('products as p', 'p.id', '=', 'os.product_id')
            ->select(
                'os.product_id',
                'os.id as order_supplier_id',
                'os.order_supplier_transaction_id',
                'os.price',
                'os.variation',
                'os.quantity',
                'ost.order_date'
            )
            ->selectRaw("
                CASE
                    WHEN os.variation = 'WHOLESALE' THEN os.quantity * p.quantity
                    ELSE os.quantity
                END as received_stock_pieces
            ")
            ->selectRaw("
                CASE
                    WHEN os.variation = 'WHOLESALE' THEN os.price
                    ELSE os.price * p.quantity
                END as completed_price_per_pack
            ")
            ->where('ost.status', 'COMPLETED')
            ->whereIn('os.product_id', $productIds)
            ->orderByDesc('ost.order_date')
            ->orderByDesc('ost.id')
            ->orderByDesc('os.id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($orders) {
                return $orders->first();
            });

        $data->transform(function ($item) use ($wholesaleCosts, $latestReceivedOrders) {
            $piecesPerPack = max((int) $item->quantity, 1);
            $currentStockPieces = (int) $item->product_stock_pieces;
            $wholesaleCost = $wholesaleCosts->get($item->product_id);
            $latestReceivedOrder = $latestReceivedOrders->get($item->product_id);
            $newStockPieces = $latestReceivedOrder
                ? (int) $latestReceivedOrder->received_stock_pieces
                : 0;
            $oldStockRemainingPieces = max($currentStockPieces - $newStockPieces, 0);
            $completedPricePerPack = $latestReceivedOrder
                ? (float) $latestReceivedOrder->completed_price_per_pack
                : null;
            $priceUpdateRequired = $wholesaleCost !== null
                && $completedPricePerPack !== null
                && abs($completedPricePerPack - (float) $item->product_price) <= 0.00001
                && abs((float) $item->product_price - $wholesaleCost) > 0.00001;

            $availableStock = $item->business_type === 'WHOLESALE'
                ? (int) $item->product_stock
                : $currentStockPieces;
            $oldStockInSellingUnit = $item->business_type === 'WHOLESALE'
                ? (int) floor($oldStockRemainingPieces / $piecesPerPack)
                : $oldStockRemainingPieces;

            $item->price_update_required = $priceUpdateRequired;
            $item->completed_capital_price = $completedPricePerPack;
            $item->new_stock_pieces = $newStockPieces;
            $item->old_stock_remaining_pieces = $oldStockRemainingPieces;
            $item->maximum_sellable_quantity = $priceUpdateRequired
                ? min($availableStock, $oldStockInSellingUnit)
                : $availableStock;
            $item->sale_blocked = $priceUpdateRequired
                && $item->maximum_sellable_quantity <= 0;
            $item->sale_block_reason = $item->sale_blocked
                ? 'Update the selling price before selling newly received stock.'
                : null;
            $item->latest_received_order = $latestReceivedOrder;

            return $item;
        });

        return response()->json($data);
    }

    /**
     * List enabled products that do not have an active markup yet.
     *
     * markup_save_payload follows the request shape accepted by store().
     */
    public function productsWithoutMarkup(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        $products = DB::table('products as p')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->select(
                'p.id as id',
                'p.id as product_id',
                'p.product_name',
                'p.price',
                'p.sale_price',
                'p.stock',
                'p.stock_pc',
                'p.quantity',
                'p.weight',
                'p.variation',
                'p.packaging',
                'p.category_id',
                'c.category_name',
                'p.brand_id',
                'b.brand_name'
            )
            ->where('p.disabled', 0)
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('mark_up_product as existing_markup')
                    ->whereColumn('existing_markup.product_id', 'p.id')
                    ->where('existing_markup.status', 1);
            })
            ->when(isset($validated['product_id']), function ($query) use ($validated) {
                $query->where('p.id', $validated['product_id']);
            })
            ->orderBy('c.ordering')
            ->orderBy('p.product_name')
            ->get();

        $products->transform(function ($product) {
            $product->price = (float) $product->price;
            $product->sale_price = (float) $product->sale_price;
            $product->stock = (int) $product->stock;
            $product->stock_pc = (int) $product->stock_pc;

            $product->markup_save_payload = [
                'product_id' => (int) $product->product_id,
                'price' => (float) $product->price,
                'mark_up_option' => null,
                'mark_up_price' => null,
                'new_price' => null,
                'profit' => null,
                'branch_stock_transaction_id' => 1,
                'business_type' => null,
            ];

            return $product;
        });

        return response()->json([
            'data' => $products,
            'count' => $products->count(),
            'save_endpoint' => '/api/markUpPrice',
            'save_method' => 'POST',
            'message' => 'Products without active markup fetched successfully.',
        ]);
    }

    /**
     * List active markups whose cost no longer matches the product cost.
     *
     * Pending supplier orders are supplementary information. A product must
     * still appear when it has no pending supplier order.
     */
    public function supplierPriceChanges(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|integer|exists:products,id',
            'include_unchanged' => 'nullable|boolean',
        ]);

        $query = DB::table('mark_up_product as mup')
            ->join('products as p', 'p.id', '=', 'mup.product_id')
            ->select(
                'p.id as product_id',
                'p.product_name',
                'p.price as product_price',
                'p.sale_price as current_sale_price',
                'p.stock as current_stock',
                'p.stock_pc as current_stock_pieces',
                'p.quantity as pieces_per_pack',
                'p.packaging',
                'p.variation as product_variation',
                'mup.id as mark_up_product_id',
                'mup.business_type',
                'mup.price as mark_up_product_price',
                'mup.mark_up_option',
                'mup.mark_up_price',
                'mup.new_price',
                'mup.profit',
                'mup.created_at as mark_up_created_at'
            )
            ->selectRaw('p.price - mup.price as price_difference')
            ->selectRaw("
                CASE
                    WHEN mup.price = 0 THEN NULL
                    ELSE ROUND((p.price - mup.price) / mup.price * 100, 2)
                END as price_change_percent
            ")
            ->where('mup.status', 1)
            ->where('p.disabled', 0)
            ->when(isset($validated['product_id']), function ($query) use ($validated) {
                $query->where('p.id', $validated['product_id']);
            })
            ->orderBy('p.product_name')
            ->orderBy('mup.business_type')
            ->orderBy('mup.id');

        $priceChanges = $query->get();

        $latestReceivedOrders = DB::table('order_supplier as received_os')
            ->join(
                'order_supplier_transaction as received_ost',
                'received_ost.id',
                '=',
                'received_os.order_supplier_transaction_id'
            )
            ->join(
                'products as received_product',
                'received_product.id',
                '=',
                'received_os.product_id'
            )
            ->select(
                'received_os.product_id',
                'received_os.id as order_supplier_id',
                'received_os.order_supplier_transaction_id',
                'received_os.price',
                'received_os.quantity',
                'received_os.variation',
                'received_ost.order_date'
            )
            ->selectRaw("
                CASE
                    WHEN received_os.variation = 'WHOLESALE'
                        THEN received_os.quantity * received_product.quantity
                    ELSE received_os.quantity
                END as received_stock_pieces
            ")
            ->selectRaw("
                CASE
                    WHEN received_os.variation = 'WHOLESALE' THEN received_os.price
                    ELSE received_os.price * received_product.quantity
                END as completed_price_per_pack
            ")
            ->where('received_ost.status', 'COMPLETED')
            ->whereIn('received_os.product_id', $priceChanges->pluck('product_id')->unique())
            ->orderByDesc('received_ost.order_date')
            ->orderByDesc('received_ost.id')
            ->orderByDesc('received_os.id')
            ->get()
            ->groupBy('product_id')
            ->map(function ($orders) {
                return $orders->first();
            });

        $priceChanges = $priceChanges
            ->groupBy('product_id')
            ->filter(function ($markups) use ($request) {
                $currentProductPrice = (float) $markups->first()->product_price;

                if ($request->boolean('include_unchanged')) {
                    return true;
                }

                $wholesaleMarkup = $markups->firstWhere('business_type', 'WHOLESALE');

                return $wholesaleMarkup
                    && abs(
                        $currentProductPrice
                        - (float) $wholesaleMarkup->mark_up_product_price
                    ) > 0.00001;
            })
            ->flatten(1)
            ->values();

        $incomingOrders = DB::table('order_supplier as os')
            ->join(
                'order_supplier_transaction as ost',
                'ost.id',
                '=',
                'os.order_supplier_transaction_id'
            )
            ->join('products as incoming_product', 'incoming_product.id', '=', 'os.product_id')
            ->join('supplier as s', 's.id', '=', 'ost.supplier_id')
            ->select(
                'os.product_id',
                'os.id as order_supplier_id',
                'os.order_supplier_transaction_id',
                'os.price as upcoming_price',
                'os.variation as upcoming_variation',
                'os.quantity as incoming_quantity',
                'ost.status as order_status',
                'ost.order_date',
                'ost.send_date',
                's.id as supplier_id',
                's.supplier_name'
            )
            ->selectRaw("
                CASE
                    WHEN os.variation = 'WHOLESALE' THEN os.price
                    ELSE os.price * incoming_product.quantity
                END as upcoming_price_per_pack
            ")
            ->selectRaw("
                CASE
                    WHEN os.variation = 'WHOLESALE' THEN os.quantity
                    ELSE os.quantity / NULLIF(incoming_product.quantity, 0)
                END as incoming_stock
            ")
            ->selectRaw("
                CASE
                    WHEN os.variation = 'WHOLESALE' THEN os.quantity * incoming_product.quantity
                    ELSE os.quantity
                END as incoming_stock_pieces
            ")
            ->whereIn('ost.status', ['PENDING', 'SEND_TO_SUPPLIER'])
            ->whereIn('os.product_id', $priceChanges->pluck('product_id')->unique())
            ->orderBy('ost.order_date')
            ->orderBy('os.id')
            ->get()
            ->groupBy('product_id');

        $priceChanges->transform(function ($item) use ($incomingOrders, $latestReceivedOrders) {
            $latestReceivedOrder = $latestReceivedOrders->get($item->product_id);
            $item->product_price = (float) $item->product_price;
            $item->mark_up_product_price = (float) $item->mark_up_product_price;
            $comparableProductPrice = $item->business_type === 'RETAIL'
                ? $item->product_price / max((int) $item->pieces_per_pack, 1)
                : $item->product_price;
            $item->price_difference = $comparableProductPrice - $item->mark_up_product_price;
            $item->price_change_percent = $item->mark_up_product_price == 0
                ? null
                : round(
                    $item->price_difference / $item->mark_up_product_price * 100,
                    2
                );

            $newStockPieces = $latestReceivedOrder
                ? (int) $latestReceivedOrder->received_stock_pieces
                : 0;

            $item->new_stock = $item->pieces_per_pack > 0
                ? $newStockPieces / $item->pieces_per_pack
                : 0;
            $item->new_stock_pieces = $newStockPieces;
            $item->old_stock_remaining_pieces = max(
                (int) $item->current_stock_pieces - $newStockPieces,
                0
            );
            $item->old_stock_remaining = $item->pieces_per_pack > 0
                ? $item->old_stock_remaining_pieces / $item->pieces_per_pack
                : 0;
            $item->old_stock_consumed = $item->old_stock_remaining_pieces <= 0;
            $item->can_change_selling_price = $item->old_stock_consumed;
            $item->latest_received_order = $latestReceivedOrder;
            $item->incoming_orders = $incomingOrders
                ->get($item->product_id, collect())
                ->map(function ($order) {
                    $order->upcoming_price = (float) $order->upcoming_price;
                    $order->upcoming_price_per_pack = (float) $order->upcoming_price_per_pack;
                    $order->incoming_stock = (float) $order->incoming_stock;
                    $order->incoming_stock_pieces = (int) $order->incoming_stock_pieces;

                    return $order;
                })
                ->values();
            $item->total_incoming_stock_pieces = $item->incoming_orders
                ->sum('incoming_stock_pieces');

            return $item;
        });

        return response()->json([
            'data' => $priceChanges,
            'count' => $priceChanges->count(),
            'as_of_date' => now('Asia/Singapore')->toDateString(),
            'message' => 'Markup price differences fetched successfully.',
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() 
    
    { 
            $data = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('branch_stock_transaction as b', 'b.id', '=', 'mup.branch_stock_transaction_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select('mup.id', 'mup.product_id', 'mup.price', 'p.disabled',
             'mup.mark_up_option', 'mup.profit', 'mup.mark_up_price', 'mup.new_price', 'mup.profit', 'mup.mark_up_option', 'p.product_name', 'p.quantity',
              'p.weight', 'p.category_id', 'p.variation', 'p.packaging', 'c.category_name', 'w.warehouse_name', 'mup.branch_stock_transaction_id', 'mup.business_type', 'p.sale_price')    
            ->selectRaw("(CASE WHEN (mup.business_type = 'WHOLESALE') THEN p.stock ELSE p.stock_pc END) as stock")
            ->where('mup.status', 1) 
            ->where('p.disabled', '=', 0)
            ->orderBy('c.ordering', 'ASC')
            ->orderBy('mup.id', 'DESC')
            ->get();

            return response()->json($data);   
    }

    public function indexLimit100() 
    
    { 
            $data = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('branch_stock_transaction as b', 'b.id', '=', 'mup.branch_stock_transaction_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select('mup.id', 'mup.product_id', 'mup.price', 'p.disabled',
             'mup.mark_up_option', 'mup.profit', 'mup.mark_up_price', 'mup.new_price', 'mup.profit', 'mup.mark_up_option', 'p.product_name', 'p.quantity',
              'p.weight', 'p.category_id', 'p.variation', 'p.packaging', 'c.category_name', 'w.warehouse_name', 'mup.branch_stock_transaction_id', 'mup.business_type', 'p.sale_price')    
            ->selectRaw("(CASE WHEN (mup.business_type = 'WHOLESALE') THEN p.stock ELSE p.stock_pc END) as stock")
            ->where('mup.status', 1) 
            ->where('p.disabled', '=', 0)
            ->orderBy('mup.id', 'DESC')
            ->limit(100)
            ->get();

            return response()->json($data);   
    }

        public function fetchMarkUpShoporder($id) 
    
    { 
            $data = DB::table('mark_up_product as mup')
            ->join('products as p', 'mup.product_id', '=', 'p.id')
            ->join('category as c', 'p.category_id', '=', 'c.id')
            ->leftJoin('branch_stock_transaction as b', 'b.id', '=', 'mup.branch_stock_transaction_id')
            ->leftJoin('warehouse as w', 'w.id', '=', 'b.warehouse_id')
            ->select('mup.id', 'mup.product_id', 'mup.price', 'p.disabled',
             'mup.mark_up_option', 'mup.mark_up_price', 'mup.price as new_price', 'mup.profit', 'mup.mark_up_option', 'p.product_name', 'p.quantity',
              'p.weight', 'p.category_id', 'p.variation', 'p.packaging', 'c.category_name', 'w.warehouse_name', 'mup.branch_stock_transaction_id', 'mup.business_type', 'p.sale_price')    
            ->selectRaw("(CASE WHEN (mup.business_type = 'WHOLESALE') THEN p.stock ELSE p.stock_pc END) as stock")
            ->selectRaw("0 AS profit")
            ->where('mup.status', 1) 
            ->where('p.disabled', '=', 0)
            ->orderBy('mup.id', 'DESC')
            ->get();

            return response()->json($data);   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required',
            'price' => 'required',
            'mark_up_option' => 'required',
            'mark_up_price' => 'required',
            'new_price' => 'required',
        ]);

           $data = DB::table('mark_up_product as mup')
           ->join('products as p', 'mup.product_id', '=', 'p.id')
           ->where('mup.status', 1)
           ->where('p.id', '=', $request->input('product_id'))
           ->update(['mup.status' => 0]);

        $markUpProduct = new MarkUpProduct;
        $markUpProduct->product_id = $request->input('product_id');
        $markUpProduct->price = $request->input('price');
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->profit = $request->input('profit');
        $markUpProduct->branch_stock_transaction_id = $request->input('branch_stock_transaction_id');
        $markUpProduct->status = 1;
        $markUpProduct->business_type = $request->input('business_type');

        $markUpProduct->save();

        return  response()->json($markUpProduct);
    }

 public function saveMarkUp(Request $request)
    {
        $this->validate($request, [
            'product_id' => 'required',
            'price' => 'required',
            'mark_up_option' => 'required',
            'mark_up_price' => 'required',
            'new_price' => 'required',
        ]);

        $markUpProduct = new MarkUpProduct;
        $markUpProduct->product_id = $request->input('product_id');
        $markUpProduct->price = $request->input('price');
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->profit = $request->input('profit');
        $markUpProduct->branch_stock_transaction_id = $request->input('branch_stock_transaction_id');
        $markUpProduct->status = 1;
        $markUpProduct->business_type = $request->input('business_type');

        $markUpProduct->save();

        return  response()->json($markUpProduct);
    }

    /**
     * Replace an active markup while retaining the previous record as history.
     */
    public function replaceMarkUp(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:mark_up_product,id',
            'product_id' => 'required|integer|exists:products,id',
            'price' => 'required|numeric|min:0',
            'mark_up_option' => 'required|in:AMOUNT,PERCENTAGE',
            'mark_up_price' => 'required|numeric|min:0',
            'new_price' => 'required|numeric|min:0',
            'profit' => 'required|numeric',
            'business_type' => 'required|in:WHOLESALE,RETAIL',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $previousMarkUp = MarkUpProduct::where('id', $validated['id'])
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $previousMarkUp->product_id !== (int) $validated['product_id']
                || $previousMarkUp->business_type !== $validated['business_type']
            ) {
                abort(422, 'The selected markup does not match the product configuration.');
            }

            if ((int) $previousMarkUp->status !== 1) {
                abort(422, 'This markup has already been replaced.');
            }

            $activeMarkUps = MarkUpProduct::where('product_id', $previousMarkUp->product_id)
                ->where('business_type', $previousMarkUp->business_type)
                ->where('status', 1);

            if ($previousMarkUp->branch_stock_transaction_id === null) {
                $activeMarkUps->whereNull('branch_stock_transaction_id');
            } else {
                $activeMarkUps->where(
                    'branch_stock_transaction_id',
                    $previousMarkUp->branch_stock_transaction_id
                );
            }

            $disabledIds = $activeMarkUps->pluck('id');

            MarkUpProduct::whereIn('id', $disabledIds)->update(['status' => 0]);

            $newMarkUp = new MarkUpProduct;
            $newMarkUp->product_id = $validated['product_id'];
            $newMarkUp->branch_stock_transaction_id = $previousMarkUp->branch_stock_transaction_id;
            $newMarkUp->price = $validated['price'];
            $newMarkUp->mark_up_option = $validated['mark_up_option'];
            $newMarkUp->mark_up_price = $validated['mark_up_price'];
            $newMarkUp->new_price = $validated['new_price'];
            $newMarkUp->profit = $validated['profit'];
            $newMarkUp->business_type = $validated['business_type'];
            $newMarkUp->status = 1;
            $newMarkUp->save();

            return [
                'data' => $newMarkUp,
                'previous_markup_id' => $previousMarkUp->id,
                'disabled_markup_ids' => $disabledIds->values(),
                'message' => 'Markup replaced successfully.',
            ];
        });

        return response()->json($result, 201);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = DB::table('mark_up_product as mup')
          ->join('products as p', 'mup.product_id', '=', 'p.id')
          ->select('p.product_name', 'p.id as product_id', 'p.stock', 'p.stock_pc', 'mup.id', 'mup.price', 'mup.mark_up_price', 'mup.mark_up_option',
           'mup.new_price', 'mup.profit', 'mup.status', 'mup.business_type')    
          ->where('mup.id', $id)
          ->first();
        return response()->json($data);
    }

        public function fetchMarkupByProductId($id)
    {
        $data = DB::table('mark_up_product as mup')
          ->join('products as p', 'mup.product_id', '=', 'p.id')
          ->select('mup.id','p.product_name', 'p.quantity',
              'p.weight', 'p.category_id', 'p.variation', 'p.packaging',  'mup.id', 'mup.price', 'mup.mark_up_price', 'mup.mark_up_option',
           'mup.new_price', 'mup.profit', 'mup.status', 'mup.business_type', 'mup.created_at')    
          ->where('p.id', $id)
          ->orderBy('mup.id', 'DESC')
          ->get();
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function edit(MarkUpProduct $markUpProduct)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $markUpProduct = MarkUpProduct::find($id);
        
        $markUpProduct->mark_up_option = $request->input('mark_up_option');
        $markUpProduct->mark_up_price = $request->input('mark_up_price');
        $markUpProduct->new_price = $request->input('new_price');
        $markUpProduct->profit = $request->input('profit');

        $markUpProduct->save();
    
        return response()->json($markUpProduct);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MarkUpProduct  $markUpProduct
     * @return \Illuminate\Http\Response
     */
    public function destroy(MarkUpProduct $markUpProduct)
    {
        $markUpProduct = MarkUpProduct::find($markUpProduct->id);
        $markUpProduct->delete();
        return response()->json($markUpProduct);
    }
}
