<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging')
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
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging')
            ->where('products.stock_warning', '>', 'products.stock')
            ->orderBy('products.id', 'DESC')
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
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging')
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
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging')
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

        if ($request->input('pack') == 'Pc') {
          $products->stock_pc  = $products->stock_pc + $request->input('newStocks');
          $products->stock  = $products->stock_pc / $products->quantity;
        } else {
          $products->stock = $products->stock + $request->input('newStocks');  
          if ($request->input('quantity') > 1) {
            $wsStocks = $request->input('quantity') * $request->input('newStocks');
            $products->stock_pc = $products->stock_pc + $wsStocks;  
          }
        }



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
