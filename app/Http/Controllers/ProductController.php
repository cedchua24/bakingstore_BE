<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockOrder;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $products = Product::all();
        // // return view('categories.index')->with('categories', $categories);
        // return response()->json($products);

            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled')
            ->orderBy('products.updated_at', 'DESC')
            ->get();
            return response()->json($data);   
    }

      public function fetchByStockWarning()
    {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled')
            ->where('products.stock_warning', '>', 'products.stock')
            ->orderBy('products.id', 'DESC')
            ->get();
            return response()->json($data);    
    }

    
        public function fetchById($id)
    {
            $data = DB::table('products as p')
            ->join('stock_order as so', 'so.product_id', '=', 'p.id')
            ->select('so.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.updated_at')
            ->orderBy('p.updated_at', 'DESC')
            ->where('p.id', $id)
            ->get();
            return response()->json($data); 
    }

            public function fetchProductByCategoryId($id)
    {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled')
            ->where('category.id', $id)
            ->orderBy('products.id', 'DESC')
            ->get();
            return response()->json($data);    
    }
                public function fetchProductByCategoryIdV2($id)
    {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled')
            ->orderBy('products.id', 'DESC')
            ->limit(20)
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
        return view('products.create');
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
            'category_id' => 'required',
            'brand_id' => 'required',
            'product_name' => 'required',
            'price' => 'required',
            'stock' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $product = new Product;
        $product->category_id = $request->input('category_id');
        $product->brand_id = $request->input('brand_id');
        $product->product_name = $request->input('product_name');
        $product->price = $request->input('price');
        $product->stock = $request->input('stock');
        $product->stock_warning = $request->input('stock_warning');
        $product->weight = $request->input('weight');
        $product->packaging = $request->input('packaging');
        $product->quantity = $request->input('quantity');
        $product->variation = $request->input('variation');
        $product->disabled = 0;

        if ($request->input('quantity') > 1) {
            $product->stock_pc = $request->input('quantity') * $request->input('stock');
        } else {
            $product->stock_pc = null;
        }

        $product->save();

        
        $productPrice = new ProductPrice;
        $productPrice->product_id = $product->id;
        $productPrice->product_price = $product->price;
        $productPrice->status = 1;
        $productPrice->save();

        $response = [
              'id' => $product->id,
              'stock' => $product->stock,
              'code' => 200,
              'message' => "Successfully Added"
          ];

        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($response);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        
        $product = Product::find($product->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($product);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $product = Product::find($product->id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $products = Product::find($product->id);
        
        $products->category_id = $request->input('category_id');
        $products->brand_id = $request->input('brand_id');
        $products->product_name = $request->input('product_name');
        $products->price = $request->input('price');
        $products->stock = $request->input('stock');
        $products->weight = $request->input('weight');
        $products->quantity = $request->input('quantity');
        $products->stock_warning = $request->input('stock_warning');
        $products->updated_at = now('GMT+8');
        $products->disabled = $request->input('disabled');

        $stockOrder = new StockOrder;
        $stockOrder->product_id = $product->id;
        $stockOrder->pack = $request->input('pack');
        $stockOrder->stock_type = $request->input('newStocks') > 0 ? "Add" : "Reduced";
        $stockOrder->stock = $request->input('newStocks');


        if ($request->input('pack') == 'Pc') {
          $stockOrder->total_stock = $products->stock_pc / $products->quantity;
          $products->stock_pc  = $products->stock_pc + $request->input('newStocks');
          $products->stock  = $products->stock_pc / $products->quantity;
         
        } else {
          $stockOrder->total_stock = $products->stock + $request->input('newStocks');  
          $products->stock = $products->stock + $request->input('newStocks');
          
          if ($request->input('quantity') > 1) {
            $wsStocks = $request->input('quantity') * $request->input('newStocks');
            $products->stock_pc = $products->stock_pc + $wsStocks;  
          }
        }


        $stockOrder->save();
        $products->save();
      

        return response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $product = Product::find($product->id);
        $product->delete();
        return response()->json($product);
    }
}
