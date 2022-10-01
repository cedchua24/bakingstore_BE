<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
            ->select('products.category_id', 'products.brand_id','category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity')
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
        $product->weight = $request->input('weight');
        $product->quantity = $request->input('quantity');

        $product->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($product);
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

        $products->save();
      

        return response()->json($products);
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
