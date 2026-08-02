<?php

namespace App\Http\Controllers;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StockOrder;
use App\Models\Spoilage;
use Carbon\Carbon;
use Mail;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

          $data = DB::table('category')
          ->join('products', 'category.id', '=', 'products.category_id')
          ->join('brand', 'brand.id', '=', 'products.brand_id')
          ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
           'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
            'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
             'products.disabled', 'products.note')
          ->orderBy('products.id', 'DESC')
          ->get();

        return response()->json($data);  
    }

    public function searchProductByName(Request $request)
    {
        $search = $request->input('search');
        $limit = $request->input('limit') ? $request->input('limit') : 50;

        $query = DB::table('products as p')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoin('vip_product_transaction as vpt', 'vpt.product_id', '=', 'p.id')
            ->leftJoin('vip_product as vp', 'vp.id', '=', 'vpt.vip_product_id')
            ->select(
                'p.id',
                'p.product_name',
                'p.category_id',
                'p.brand_id',
                'p.disabled',
                'c.category_name',
                'b.brand_name',
                DB::raw("GROUP_CONCAT(DISTINCT CONCAT(COALESCE(vp.vip_product_name, ''), '::', COALESCE(vp.vip_color, '')) SEPARATOR '||') as vip_product_list")
            )
            ->where('p.disabled', 0);

        if ($search != '') {
            $query->where('p.product_name', 'like', '%' . $search . '%');
        }

        $data = $query
            ->groupBy(
                'p.id',
                'p.product_name',
                'p.category_id',
                'p.brand_id',
                'p.disabled',
                'c.category_name',
                'b.brand_name'
            )
            ->orderBy('p.product_name', 'asc')
            ->limit($limit)
            ->get();

        foreach ($data as $item) {
            $vipProducts = [];

            if ($item->vip_product_list != '') {
                foreach (explode('||', $item->vip_product_list) as $vipProduct) {
                    $vipProductDetails = explode('::', $vipProduct);

                    if ($vipProductDetails[0] != '') {
                        $vipProducts[] = [
                            'vip_product_name' => $vipProductDetails[0],
                            'vip_color' => isset($vipProductDetails[1]) ? $vipProductDetails[1] : '',
                        ];
                    }
                }
            }

            $item->vip_products = $vipProducts;
            unset($item->vip_product_list);
        }

        return response()->json($data);
    }

        public function fetchProductEnabled()
    {

          $data = DB::table('category')
          ->join('products', 'category.id', '=', 'products.category_id')
          ->join('brand', 'brand.id', '=', 'products.brand_id')
          ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
           'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
            'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
             'products.disabled', 'products.note')
          ->orderBy('products.id', 'DESC')
          ->where('products.disabled', 0)
          ->get();

        return response()->json($data);  
    }

     public function fetchProductListV2($id)
    {

            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
            ->orderBy('products.updated_at', 'DESC')
            ->get();


            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->first();

           $response = [
              'total_value' =>$total_value,
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);   
    }

         public function fetchProductToNotify($id)
    {
        if ($id == 0) {
            $data = DB::table('products')
            ->join('category', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->join('out_of_stock_update as os', 'os.product_id', '=', 'products.id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
            ->orderBy('products.updated_at', 'DESC')
            ->groupBy('products.id')
            ->get();
        } else {
           $data = DB::table('products')
            ->join('category', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->join('out_of_stock_update as os', 'os.product_id', '=', 'products.id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
              ->where('category.id', $id)
            ->orderBy('products.updated_at', 'DESC')
            ->groupBy('products.id')
            ->get();
        }




           $response = [
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);   
    }

         public function fetchProductListDisabled($id)
    {
        if ($id == 0) {
          $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
            ->orderBy('products.updated_at', 'DESC')
            ->where('products.disabled', 1)
            ->get();


            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->where('products.disabled', 1)
            ->first();

        } else {
          $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
            ->orderBy('products.updated_at', 'DESC')
            ->where('products.disabled', 1)
            ->where('category.id',$id)
            ->get();


            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->where('products.disabled', 1)
            ->where('category.id',$id)
            ->first();

        }

           $response = [
              'total_value' =>$total_value,
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);   
    }

    public function fetchProductListNote($id)
    {
        if ($id == 0) {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
            ->orderBy('products.updated_at', 'DESC')
            ->where('products.note', '!=', '')
            ->get();


            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->first();
        } else {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled', 'products.note')
            ->orderBy('products.updated_at', 'DESC')
            ->where('products.note', '!=', '')
            ->where('category.id', $id)
            ->get();


            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->where('category.id' ,$id)
            ->first();
        }


           $response = [
              'total_value' =>$total_value,
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);   
    }

    public function fetchOrderSupplierExpirationList($id)
    {
        $data = DB::table('order_supplier as os')
         ->join('products as p', 'p.id', '=', 'os.product_id')
         ->select('os.id', 'os.price', 'os.expiration', 'os.enable', 'os.created_at')
         ->where('p.id', $id)
         ->where('os.expiration', '!=', '0000-00-00')
        
         ->get();
        return response()->json($data);  
    }

    public function fetchProductListExpiration($id)
    {
        if ($id == 0) {
          $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->join('order_supplier as os', 'os.product_id', '=', 'products.id')
            ->join('order_supplier_transaction as ost', 'ost.id', '=', 'os.order_supplier_transaction_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled',
              'os.expiration', 'products.note')
            ->where('os.expiration', '!=', '0000-00-00')
            ->where('os.enable', 1) 
            ->where('ost.status', 'COMPLETED') 
            ->where('products.disabled', '==', 0) 
            ->where('products.stock', '!=', 0) 
            ->groupBy('products.id')
            ->orderBy('os.expiration', 'ASC')
            ->get();
            
            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->first();

        } else {
                        $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->join('order_supplier as os', 'os.product_id', '=', 'products.id')
            ->join('order_supplier_transaction as ost', 'ost.id', '=', 'os.order_supplier_transaction_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled',
              'os.expiration', 'products.note')
            ->where('os.expiration', '!=', '0000-00-00')
            ->where('os.enable', 1) 
            ->where('ost.status', 'COMPLETED') 
            ->where('products.disabled', '==', 0) 
            ->where('products.stock', '!=', 0) 
            ->where('category.id',  $id)
            ->groupBy('products.id')
            ->orderBy('os.expiration', 'ASC')
            ->get();
            
            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->first();

        }



           $response = [
              'total_value' =>$total_value,
              'today' => date('Y-m-d'),
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);   
    }

        public function fetchProductValue($id)
    {
        // $products = Product::all();
        // // return view('categories.index')->with('categories', $categories);
        // return response()->json($products);
 
      if ($id == 0) {

            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('mark_up_product as mup', 'mup.product_id', '=', 'products.id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price', 'mup.price as mup_price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled',
              'mup.new_price', 'mup.profit')
            ->orderBy('products.updated_at', 'DESC')
            ->where('mup.business_type', 'WHOLESALE')
            ->where('mup.status', 1)
            ->get();

            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('mark_up_product as mup', 'mup.product_id', '=', 'products.id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'), DB::raw('SUM(mup.new_price * products.stock) as total_new_value')
            , DB::raw('SUM(mup.profit * products.stock) as total_profit'))  
            ->where('mup.business_type', 'WHOLESALE')  
            ->where('mup.status', 1)
            ->first();

      } else {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('mark_up_product as mup', 'mup.product_id', '=', 'products.id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.stock_warning', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price', 'mup.price as mup_price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging', 'products.disabled',
              'mup.new_price', 'mup.profit')
            ->orderBy('products.updated_at', 'DESC')
            ->where('category.id', $id)
            ->where('mup.business_type', 'WHOLESALE')
            ->where('mup.status', 1)
            ->get();

            $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('mark_up_product as mup', 'mup.product_id', '=', 'products.id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'), DB::raw('SUM(mup.new_price * products.stock) as total_new_value')
            , DB::raw('SUM(mup.profit * products.stock) as total_profit'))  
            ->where('category.id', $id)
            ->where('mup.business_type', 'WHOLESALE')  
            ->where('mup.status', 1)
            ->first();
      }
     $myArray = ["one", "two", "three", "four"];
      unset($myArray[0]);
      $myArray = array_values($myArray);

      // unset($data[0]);
      // $data = array_values($data);

           $response = [
              'total_value' =>$total_value,
               'myArray' =>$myArray,
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);   
    }

          public function fetchOutOfStock($category_id)
    {
        if ($category_id == 0) {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note', 'products.updated_at')
            ->where('products.disabled', 0)
            ->where('products.stock', 0)
            ->where('products.stock_pc', 0)
            ->orderBy('products.updated_at', 'desc')
            ->get();
        } else {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note', 'products.updated_at')
            ->where('products.disabled',  0)
            ->where('products.stock', 0)
            ->where('products.stock_pc', 0)
            ->where('category.id',  $category_id)
            ->orderBy('products.stock', 'ASC')
                ->get();
        }

        $this->attachPendingSupplierOrders($data);

        $response = [
              'data' => $data,
              'id' => $category_id,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);    
    }

       public function fetchStockWarningPerSupplier($supplier_id)
    {

            $data = DB::table('product_supplier as ps')
            ->join('supplier as s', 's.id', '=', 'ps.supplier_id')
            ->join('products', 'products.id', '=', 'ps.product_id')
            ->join('category', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('s.supplier_name', 'products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'products.stock_warning_type', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note')
            ->where('ps.supplier_id',  $supplier_id)
            ->where(function ($query) {
                    $query->where(function ($q) {
                        // WHOLESALE → use stock
                        $q->where('products.stock_warning_type', 'WHOLESALE')
                        ->where('products.stock', '!=', 0)
                        ->whereColumn('products.stock', '<', 'products.stock_warning');
                    })
                    ->orWhere(function ($q) {
                        // RETAIL / others → use stock_pc
                        $q->where('products.stock_warning_type', '!=', 'WHOLESALE')
                        ->where('products.stock_pc', '!=', 0)
                        ->whereColumn('products.stock_pc', '<', 'products.stock_warning');
                    });
                })
            ->where('products.stock_warning', '!=', 0)
            ->where('products.disabled', 0)
            ->orderBy('products.stock', 'ASC')
            ->get();
            
        $this->attachPendingSupplierOrders($data);

        $response = [
              'data' => $data,
              'id' => $supplier_id,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);    
    }

           public function fetchStockPerSupplier($supplier_id)
    {

            $data = DB::table('product_supplier as ps')
            ->join('supplier as s', 's.id', '=', 'ps.supplier_id')
            ->join('products', 'products.id', '=', 'ps.product_id')
            ->join('category', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('s.supplier_name', 'products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note')
            ->where('ps.supplier_id',  $supplier_id)
            ->orderBy('products.stock', 'DESC')
            ->get();

        $this->attachPendingSupplierOrders($data);

        $response = [
              'data' => $data,
              'id' => $supplier_id,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);    
    }

      public function fetchByStockWarning($category_id)
    {
        if ($category_id == 0) {
            $data = DB::table('category')
                ->join('products', 'category.id', '=', 'products.category_id')
                ->join('brand', 'brand.id', '=', 'products.brand_id')
                ->select(
                    'products.category_id',
                    'products.brand_id',
                    'products.variation',
                    'products.stock_warning',
                    'category.category_name',
                    'brand.brand_name',
                    'products.id',
                    'products.product_name',
                    'products.price',
                    'products.stock',
                    'products.weight',
                    'products.quantity',
                    'products.stock_pc',
                    'products.packaging',
                    'products.disabled',
                    'products.note',
                    'products.stock_warning_type'
                )
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // WHOLESALE → use stock
                        $q->where('products.stock_warning_type', 'WHOLESALE')
                        ->where('products.stock', '!=', 0)
                        ->whereColumn('products.stock', '<', 'products.stock_warning');
                    })
                    ->orWhere(function ($q) {
                        // RETAIL / others → use stock_pc
                        $q->where('products.stock_warning_type', '!=', 'WHOLESALE')
                        ->where('products.stock_pc', '!=', 0)
                        ->whereColumn('products.stock_pc', '<', 'products.stock_warning');
                    });
                })
                ->where('products.stock_warning', '!=', 0)
                ->where('products.disabled', 0)
                ->orderBy('products.stock', 'ASC')
                ->get();
        } else {

              $data = DB::table('category')
                ->join('products', 'category.id', '=', 'products.category_id')
                ->join('brand', 'brand.id', '=', 'products.brand_id')
                ->select(
                    'products.category_id',
                    'products.brand_id',
                    'products.variation',
                    'products.stock_warning',
                    'category.category_name',
                    'brand.brand_name',
                    'products.id',
                    'products.product_name',
                    'products.price',
                    'products.stock',
                    'products.weight',
                    'products.quantity',
                    'products.stock_pc',
                    'products.packaging',
                    'products.disabled',
                    'products.note',
                    'products.stock_warning_type'
                )
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // WHOLESALE → use stock
                        $q->where('products.stock_warning_type', 'WHOLESALE')
                        ->where('products.stock', '!=', 0)
                        ->whereColumn('products.stock', '<', 'products.stock_warning');
                    })
                    ->orWhere(function ($q) {
                        // RETAIL / others → use stock_pc
                        $q->where('products.stock_warning_type', '!=', 'WHOLESALE')
                        ->where('products.stock_pc', '!=', 0)
                        ->whereColumn('products.stock_pc', '<', 'products.stock_warning');
                    });
                })
                ->where('products.stock_warning', '!=', 0)
                ->where('products.disabled', 0)
                ->where('products.stock_pc', '!=', 0)
                ->where('category.id',  $category_id)
                ->orderBy('products.stock', 'ASC')
                ->get();
        }

        $this->attachPendingSupplierOrders($data);

        $response = [
              'data' => $data,
              'id' => $category_id,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);    
    }

    private function attachPendingSupplierOrders($data)
    {
        $productIds = $data->pluck('id')->unique()->values();
        $pendingOrdersByProduct = collect();

        if ($productIds->isNotEmpty()) {
            $pendingOrdersByProduct = DB::table('order_supplier as os')
                ->join(
                    'order_supplier_transaction as ost',
                    'ost.id',
                    '=',
                    'os.order_supplier_transaction_id'
                )
                ->join('supplier as s', 's.id', '=', 'ost.supplier_id')
                ->join('products as p', 'p.id', '=', 'os.product_id')
                ->select(
                    'os.product_id',
                    'os.order_supplier_transaction_id',
                    'ost.order_date as date',
                    'ost.status',
                    'ost.send_date',
                    's.supplier_name as supplier'
                )
                ->selectRaw("
                    CONCAT(
                        os.quantity,
                        ' ',
                        CASE
                            WHEN os.variation = 'WHOLESALE' THEN p.packaging
                            ELSE p.variation
                        END
                    ) as quantity
                ")
                ->whereIn('os.product_id', $productIds)
                ->whereIn('ost.status', ['PENDING', 'SEND_TO_SUPPLIER'])
                ->orderBy('ost.order_date', 'desc')
                ->orderBy('ost.id', 'desc')
                ->get()
                ->groupBy('product_id');
        }

        foreach ($data as $product) {
            $product->pending_orders = $pendingOrdersByProduct
                ->get($product->id, collect())
                ->map(function ($pendingOrder) {
                    return [
                        'order_supplier_transaction_id' => $pendingOrder->order_supplier_transaction_id,
                        'date' => $pendingOrder->date,
                        'supplier' => $pendingOrder->supplier,
                        'quantity' => $pendingOrder->quantity,
                        'status' => $pendingOrder->status,
                        'send_date' => $pendingOrder->status === 'SEND_TO_SUPPLIER'
                            ? $pendingOrder->send_date
                            : null,
                    ];
                })
                ->values();
        }
    }

    public function fetchProductMonthlySales(Request $request)
    {
        $this->validate($request, [
            'month' => 'nullable|date_format:Y-m',
            'customer_id' => 'required|integer|exists:customer,id',
            'category_id' => 'nullable|integer|exists:category,id',
            'include_disabled' => 'nullable|boolean',
            'comparison_page' => 'nullable|integer|min:0|max:40',
        ]);

        $reportMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $comparisonPage = (int) $request->input('comparison_page', 0);
        $firstComparisonMonthAgo = ($comparisonPage * 3) + 1;
        $monthOffsets = collect([0])->concat(
            range($firstComparisonMonthAgo, $firstComparisonMonthAgo + 2)
        );

        $months = $monthOffsets->map(function ($monthsAgo) use ($reportMonth) {
            $month = $reportMonth->copy()->subMonths($monthsAgo);

            return [
                'month' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
                'date_from' => $month->copy()->startOfMonth()->toDateString(),
                'date_to' => $month->copy()->endOfMonth()->toDateString(),
            ];
        });

        $monthlySalesQuery = DB::table('shop_order as so')
            ->join('shop_order_transaction as sot', 'sot.id', '=', 'so.shop_transaction_id')
            ->join('products as sold_product', 'sold_product.id', '=', 'so.product_id')
            ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->select(
                'so.product_id',
                DB::raw("DATE_FORMAT(sot.date, '%Y-%m') as sold_month"),
                DB::raw('SUM(so.shop_order_quantity) as quantity_sold'),
                DB::raw("SUM(CASE WHEN mup.business_type = 'WHOLESALE' THEN so.shop_order_quantity * sold_product.quantity ELSE so.shop_order_quantity END) as pieces_sold"),
                DB::raw('SUM(so.shop_order_total_price) as sales_amount'),
                DB::raw('SUM(so.shop_order_profit) as profit_amount'),
                DB::raw('COUNT(DISTINCT sot.id) as order_count'),
                DB::raw('COUNT(DISTINCT sot.requestor) as customer_count')
            )
            ->where('sot.type', 0)
            ->where('sot.status', 1)
            ->whereBetween('sot.date', [
                $reportMonth->copy()->subMonths($firstComparisonMonthAgo + 2)->startOfMonth()->toDateString(),
                $reportMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->where('sot.requestor', $request->input('customer_id'))
            ->groupBy('so.product_id', DB::raw("DATE_FORMAT(sot.date, '%Y-%m')"));

        $salesByProductAndMonth = $monthlySalesQuery->get()->groupBy('product_id');

        $businessTypes = DB::table('mark_up_product')
            ->select(
                'product_id',
                DB::raw("GROUP_CONCAT(DISTINCT business_type ORDER BY business_type SEPARATOR ',') as business_types")
            )
            ->groupBy('product_id');

        $products = DB::table('products as p')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->leftJoinSub($businessTypes, 'product_business_types', function ($join) {
                $join->on('product_business_types.product_id', '=', 'p.id');
            })
            ->select(
                'p.id as product_id',
                'p.product_name',
                'p.category_id',
                'c.category_name',
                'p.brand_id',
                'b.brand_name',
                'p.stock',
                'p.stock_pc',
                'p.stock_warning',
                'p.stock_warning_type',
                'p.packaging',
                'p.variation',
                'p.quantity',
                'p.disabled',
                DB::raw("COALESCE(product_business_types.business_types, '') as business_types")
            )
            ->when(!$request->boolean('include_disabled'), function ($query) {
                $query->where('p.disabled', 0);
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('p.category_id', $request->input('category_id'));
            })
            ->whereExists(function ($customerProducts) use ($request) {
                $customerProducts
                    ->select(DB::raw(1))
                    ->from('shop_order as customer_so')
                    ->join(
                        'shop_order_transaction as customer_sot',
                        'customer_sot.id',
                        '=',
                        'customer_so.shop_transaction_id'
                    )
                    ->whereColumn('customer_so.product_id', 'p.id')
                    ->where('customer_sot.requestor', $request->input('customer_id'))
                    ->where('customer_sot.type', 0)
                    ->where('customer_sot.status', 1);
            })
            ->orderBy('p.product_name')
            ->get()
            ->map(function ($product) use ($months, $salesByProductAndMonth) {
                $productSales = $salesByProductAndMonth
                    ->get($product->product_id, collect())
                    ->keyBy('sold_month');

                $monthlySales = $months->map(function ($month) use ($productSales) {
                    $sales = $productSales->get($month['month']);

                    return array_merge($month, [
                        'quantity_sold' => (int) ($sales->quantity_sold ?? 0),
                        'pieces_sold' => (int) ($sales->pieces_sold ?? 0),
                        'sales_amount' => round((float) ($sales->sales_amount ?? 0), 2),
                        'profit_amount' => round((float) ($sales->profit_amount ?? 0), 2),
                        'order_count' => (int) ($sales->order_count ?? 0),
                        'customer_count' => (int) ($sales->customer_count ?? 0),
                    ]);
                })->values();

                $current = $monthlySales->first();
                $previousMonths = $monthlySales->slice(1)->values();
                $lastMonth = $previousMonths->first();
                $averageSales = round((float) $previousMonths->avg('sales_amount'), 2);
                $averageProfit = round((float) $previousMonths->avg('profit_amount'), 2);
                $averageQuantity = round((float) $previousMonths->avg('quantity_sold'), 2);
                $averagePieces = round((float) $previousMonths->avg('pieces_sold'), 2);
                $salesLastMonthGap = round($current['sales_amount'] - $lastMonth['sales_amount'], 2);

                if ($current['sales_amount'] == 0) {
                    $salesStatus = $averageSales > 0 ? 'NO_SALES' : 'NO_SALES_4_MONTHS';
                } elseif ($current['sales_amount'] < $averageSales) {
                    $salesStatus = 'LOW_SALES';
                } elseif ($current['sales_amount'] > $averageSales) {
                    $salesStatus = 'HIGH_SALES';
                } else {
                    $salesStatus = 'UNCHANGED';
                }

                $product->business_types = $product->business_types !== ''
                    ? explode(',', $product->business_types)
                    : [];
                $product->current_month = $current;
                $product->previous_months = $previousMonths;
                $product->average_sales = $averageSales;
                $product->average_profit = $averageProfit;
                $product->average_quantity = $averageQuantity;
                $product->average_pieces = $averagePieces;
                $product->sales_average_gap = round($current['sales_amount'] - $averageSales, 2);
                $product->profit_average_gap = round($current['profit_amount'] - $averageProfit, 2);
                $product->quantity_average_gap = round($current['quantity_sold'] - $averageQuantity, 2);
                $product->pieces_average_gap = round($current['pieces_sold'] - $averagePieces, 2);
                $product->sales_last_month_gap = $salesLastMonthGap;
                $product->profit_last_month_gap = round($current['profit_amount'] - $lastMonth['profit_amount'], 2);
                $product->quantity_last_month_gap = round($current['quantity_sold'] - $lastMonth['quantity_sold'], 2);
                $product->pieces_last_month_gap = round($current['pieces_sold'] - $lastMonth['pieces_sold'], 2);
                $product->sales_change_percentage = $lastMonth['sales_amount'] > 0
                    ? round(($salesLastMonthGap / $lastMonth['sales_amount']) * 100, 2)
                    : null;
                $product->sales_trend = $salesLastMonthGap > 0
                    ? 'HIGHER'
                    : ($salesLastMonthGap < 0 ? 'LOWER' : 'UNCHANGED');
                $product->sales_status = $salesStatus;

                return $product;
            })
            ->sort(function ($first, $second) {
                $averageSalesComparison = $second->average_sales <=> $first->average_sales;

                if ($averageSalesComparison !== 0) {
                    return $averageSalesComparison;
                }

                $salesComparison = $second->current_month['sales_amount'] <=> $first->current_month['sales_amount'];

                if ($salesComparison !== 0) {
                    return $salesComparison;
                }

                $piecesComparison = $second->current_month['pieces_sold'] <=> $first->current_month['pieces_sold'];

                return $piecesComparison !== 0
                    ? $piecesComparison
                    : $second->average_pieces <=> $first->average_pieces;
            })
            ->values();

        $monthTotals = $months->map(function ($month, $monthIndex) use ($products) {
            return array_merge($month, [
                'quantity_sold' => $products->sum(function ($product) use ($monthIndex) {
                    return $monthIndex === 0
                        ? $product->current_month['quantity_sold']
                        : $product->previous_months[$monthIndex - 1]['quantity_sold'];
                }),
                'pieces_sold' => $products->sum(function ($product) use ($monthIndex) {
                    return $monthIndex === 0
                        ? $product->current_month['pieces_sold']
                        : $product->previous_months[$monthIndex - 1]['pieces_sold'];
                }),
                'sales_amount' => round($products->sum(function ($product) use ($monthIndex) {
                    return $monthIndex === 0
                        ? $product->current_month['sales_amount']
                        : $product->previous_months[$monthIndex - 1]['sales_amount'];
                }), 2),
                'profit_amount' => round($products->sum(function ($product) use ($monthIndex) {
                    return $monthIndex === 0
                        ? $product->current_month['profit_amount']
                        : $product->previous_months[$monthIndex - 1]['profit_amount'];
                }), 2),
            ]);
        })->values();

        $currentTotals = $monthTotals->first();
        $previousTotals = $monthTotals->slice(1)->values();
        $averageSales = round((float) $previousTotals->avg('sales_amount'), 2);
        $averageProfit = round((float) $previousTotals->avg('profit_amount'), 2);
        $earliestSaleDate = DB::table('shop_order_transaction')
            ->where('requestor', $request->input('customer_id'))
            ->where('type', 0)
            ->where('status', 1)
            ->min('date');
        $oldestComparisonMonth = $reportMonth->copy()
            ->subMonths($firstComparisonMonthAgo + 2)
            ->startOfMonth();
        $hasOlderComparison = $comparisonPage < 40
            && $earliestSaleDate
            && Carbon::parse($earliestSaleDate)->startOfMonth()->lt($oldestComparisonMonth);

        return response()->json([
            'report_month' => $months->first(),
            'comparison' => [
                'page' => $comparisonPage,
                'previous_page' => $comparisonPage > 0 ? $comparisonPage - 1 : null,
                'next_page' => $hasOlderComparison ? $comparisonPage + 1 : null,
                'newer_page' => $comparisonPage > 0 ? $comparisonPage - 1 : null,
                'older_page' => $hasOlderComparison ? $comparisonPage + 1 : null,
                'has_newer' => $comparisonPage > 0,
                'has_older' => (bool) $hasOlderComparison,
                'months' => $months->slice(1)->values(),
            ],
            'filters' => [
                'customer_id' => (int) $request->input('customer_id'),
                'category_id' => $request->filled('category_id') ? (int) $request->input('category_id') : null,
                'include_disabled' => $request->boolean('include_disabled'),
                'comparison_page' => $comparisonPage,
            ],
            'current_month' => $currentTotals,
            'previous_months' => $previousTotals,
            'average_sales' => $averageSales,
            'average_profit' => $averageProfit,
            'sales_average_gap' => round($currentTotals['sales_amount'] - $averageSales, 2),
            'profit_average_gap' => round($currentTotals['profit_amount'] - $averageProfit, 2),
            'counts' => [
                'total_products' => $products->count(),
                'no_sales' => $products->whereIn('sales_status', ['NO_SALES', 'NO_SALES_4_MONTHS'])->count(),
                'low_sales' => $products->where('sales_status', 'LOW_SALES')->count(),
                'high_sales' => $products->where('sales_status', 'HIGH_SALES')->count(),
                'unchanged' => $products->where('sales_status', 'UNCHANGED')->count(),
            ],
            'data' => $products,
        ]);
    }

          public function fetchNoStockWarning($category_id)
    {
        if ($category_id == 0) {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note')
            ->where('products.stock_warning', '==', 0)
            ->orderBy('products.stock', 'ASC')
            ->get();
        } else {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'products.stock_warning', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note')
            ->where('products.stock_warning', '==', 0)
            ->where('category.id',  $category_id)
            ->orderBy('products.stock', 'ASC')
            ->get();
        }

        $response = [
              'data' => $data,
              'id' => $category_id,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);    
    }

          public function fetchModifiedStockDaily(Request $request, $date)
    {
         $stock_order_ids = Spoilage::all('stock_order_id');
        $typeList = $request->input('typeList', []);
        $typeList = is_array($typeList) ? $typeList : [$typeList];
        $typeList = array_values(array_filter($typeList, function ($type) {
            return $type !== null && $type !== '';
        }));

        if ($date === 'undefined') {
            $data = DB::table('stock_order as so')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            // ->rightJoin('spoilage as sl', 'sl.stock_order_id', '=', 'so.id')
            ->select('so.id', 'so.updated_at', 'so.stock_reason', 'so.stock', 'so.pack', 'so.type', 'so.price', 'so.total_cost', 'p.product_name', 'b.brand_name')
            ->where('so.updated_at', 'like', date('Y-m-d').'%')
            ->when(count($typeList) > 0, function ($query) use ($typeList) {
                $query->whereIn('so.type', $typeList);
            })
            ->whereNotIn('so.id',  Spoilage::all('stock_order_id'))
            ->orderBy('so.id', 'desc')
            ->get();

            // $newDateFormat2 = date('Y-m-d', strtotime($data[0]->updated_at));
        } else {
            $data = DB::table('stock_order as so')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            // ->rightJoin('spoilage as sl', 'sl.stock_order_id', '=', 'so.id')
            ->select('so.id', 'so.updated_at', 'so.stock_reason', 'so.stock', 'so.pack', 'so.type', 'so.price', 'so.total_cost', 'p.product_name', 'b.brand_name')
            ->where('so.updated_at', 'like', $date.'%')
            ->when(count($typeList) > 0, function ($query) use ($typeList) {
                $query->whereIn('so.type', $typeList);
            })
            ->whereNotIn('so.id',  Spoilage::all('stock_order_id'))
            ->orderBy('so.id', 'desc')
            ->get();

        }
           $response = [
              'data' => $data,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);
    }

       public function fetchModifiedReportList(Request $request)
    {
       $stock_order_ids = Spoilage::all('stock_order_id');
       $typeList = $request->input('typeList', []);
       $typeList = is_array($typeList) ? $typeList : [$typeList];
       $typeList = array_values(array_filter($typeList, function ($type) {
           return $type !== null && $type !== '';
       }));

       if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '') {
           $tst = 2;
            $data = DB::table('stock_order as so')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->join('category as c', 'c.id', '=', 'p.category_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            ->select('so.id', 'so.updated_at', 'so.stock_reason', 'so.stock', 'so.pack', 'so.type', 'so.price', 'so.total_cost','p.product_name', 'b.brand_name')
            ->when(count($typeList) > 0, function ($query) use ($typeList) {
                $query->whereIn('so.type', $typeList);
            })
            ->whereNotIn('so.id',  Spoilage::all('stock_order_id'))
            ->orderBy('so.id', 'desc')
            ->get();

            // $newDateFormat2 = date('Y-m-d', strtotime($data[0]->updated_at));
        } else {
            $tst = 1;
            $data = DB::table('stock_order as so')
            ->join('products as p', 'p.id', '=', 'so.product_id')
            ->join('brand as b', 'b.id', '=', 'p.brand_id')
            // ->rightJoin('spoilage as sl', 'sl.stock_order_id', '=', 'so.id')
            ->select('so.id', 'so.updated_at', 'so.stock_reason', 'so.stock', 'so.pack', 'so.type', 'so.price', 'so.total_cost', 'p.product_name', 'b.brand_name')
            ->where('so.created_at', '>=', $request->input('dateFrom'))
            ->where('so.created_at', '<=', $request->input('dateTo'))
            ->when(count($typeList) > 0, function ($query) use ($typeList) {
                $query->whereIn('so.type', $typeList);
            })
            ->whereNotIn('so.id',  Spoilage::all('stock_order_id'))
            ->orderBy('so.id', 'desc')
            ->get();

        }
           $response = [
              'data' => $data,
                'tst' => $tst,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];
            return response()->json($response);
    }

    
        public function fetchById($id)
    {
            $data = DB::table('products as p')
            ->join('stock_order as so', 'so.product_id', '=', 'p.id')
            ->select('so.id', 'p.product_name', 'so.pack', 'so.stock_type', 'so.stock',
             'so.stock_reason', 'so.updated_at')
            ->orderBy('so.id', 'DESC')
            ->where('p.id', $id)
            ->get();
            return response()->json($data); 
    }

            public function fetchProductByCategoryId($id)
    {
        if ($id == 0) {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled','products.stock_warning', 'products.stock_warning_type',   'products.note')
            ->where('products.disabled', 0)
            ->orderBy('products.id', 'DESC')
            ->get();

             $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->where('products.disabled', 0)
            ->first();

        } else {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc','products.stock_warning_type', 'products.packaging',
               'products.disabled', 'products.stock_warning', 'products.note')
            ->where('category.id', $id)
            ->where('products.disabled', 0)
            ->orderBy('products.id', 'DESC')
            ->get();

             $total_value = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select(DB::raw('SUM(products.price * products.stock) as total_price'))   
            ->where('products.disabled', 0)
            ->where('category.id', $id)
            ->first();

        }

        $this->attachPendingSupplierOrders($data);

           $response = [
              'total_value' =>$total_value,
              'data' => $data,
              'code' => 200,
              'message' => "Successfully Addedz"
          ];

          return response()->json($response);     
    }
        public function fetchProductByCategoryIdV2($id)
    {
            $data = DB::table('category')
            ->join('products', 'category.id', '=', 'products.category_id')
            ->join('brand', 'brand.id', '=', 'products.brand_id')
            ->select('products.category_id', 'products.brand_id', 'products.variation', 'category.category_name',
             'brand.brand_name', 'products.id', 'products.product_name', 'products.price',
              'products.stock', 'products.weight', 'products.quantity', 'products.stock_pc', 'products.packaging',
               'products.disabled', 'products.note')
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

     public function testController(Request $request)
     {
        $data = DB::table('products as p')
        ->select('p.id', 'p.stock', 'p.stock_pc', 'p.quantity')
        ->where('p.disabled', 0)
        // ->where('p.id', '>', 1)
        // ->where('p.id', '<', 101)
        ->get();

        for($x=0; $x<= sizeof($data)-1; $x++) {
           
            $product = Product::find($data[$x]->id);
            $product->stock = floor($product->stock_pc / $product->quantity);
            $product->save();
        }
        return  response()->json($data);
     }

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
        $product->stock_warning_type = $request->input('stock_warning_type');
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

        public function getUnsoldProducts(Request $request)
        {
            $dateFrom = $request->input('dateFrom');
            $dateTo   = $request->input('dateTo');
            $supplier_id = $request->input('supplier_id');
            $category_id = $request->input('category_id', $request->input('categoryId'));

            $data = collect();

            if (!empty($dateFrom) && !empty($dateTo)) {
                $query = DB::table('products as p')
                    ->whereNotExists(function ($query) use ($dateFrom, $dateTo) {
                        $query->select(DB::raw(1))
                            ->from('shop_order as so')
                            ->whereColumn('so.product_id', 'p.id')
                            ->whereBetween('so.created_at', [
                                Carbon::parse($dateFrom)->startOfDay(),
                                Carbon::parse($dateTo)->endOfDay()
                            ]);
                    })
                    ->where('p.disabled', 0)
                    ->where('p.stock', '!=', 0)
                    ->select(
                        'p.*',

                        // ✅ total_value
                        DB::raw("
                            CASE 
                                WHEN p.quantity > 1 
                                THEN (p.price / p.quantity) * p.stock_pc
                                ELSE p.price * p.stock
                            END as total_value
                        "),

                        // ✅ last_sold_at
                        DB::raw("
                            (
                                SELECT MAX(so2.created_at)
                                FROM shop_order as so2
                                WHERE so2.product_id = p.id
                            ) as last_sold_at
                        ")
                    )
                    ->orderBy('p.stock', 'desc');

                if ($supplier_id) {
                    $query->leftJoin('product_supplier as ps', 'ps.product_id', '=', 'p.id')
                        ->leftJoin('supplier as s', 's.id', '=', 'ps.supplier_id')
                        ->where('ps.supplier_id', $supplier_id);
                }

                if ($category_id) {
                    $query->where('p.category_id', $category_id);
                }

                $data = $query->get();
            }

            return response()->json([
                'data' => $data,
                'code' => 200,
                'message' => 'Success'
            ]);
        }

    public function fetchPendingProduct(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo   = $request->input('dateTo');
        $status   = $request->input('status');

        $data = DB::table('products as p')
            ->join('shop_order as so', 'p.id', '=', 'so.product_id')
            ->join('shop_order_transaction as sot', 'so.shop_transaction_id', '=', 'sot.id')
            ->join('shop', 'shop.id', '=', 'sot.shop_id')
            ->leftJoin('mark_up_product as mup', 'mup.id', '=', 'so.mark_up_product_id')
            ->where('sot.is_pickup', 0)
            ->where('shop.shop_type_id', 3)

            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('sot.status', $status);
            })

            ->when($dateFrom && $dateTo, function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('sot.date', [$dateFrom, $dateTo]);
            })

            ->select(
                'p.*',
                DB::raw("
                    SUM(
                        CASE 
                            WHEN p.quantity = 1 THEN so.shop_order_quantity
                            WHEN mup.business_type = 'WHOLESALE' THEN so.shop_order_quantity * p.quantity
                            ELSE so.shop_order_quantity
                        END
                    ) as total_quantity
                ")
            )
            ->groupBy('p.id')
            ->get();

        return response()->json([
            'data' => $data,
            'code' => 200,
            'message' => 'Success'
        ]);
    }

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
        $products->sale_price = $request->input('sale_price');
        $products->stock = $request->input('stock');
        $products->weight = $request->input('weight');
        $products->quantity = $request->input('quantity');
        $products->variation = $request->input('variation');
        $products->packaging = $request->input('packaging');
        $products->stock_warning = $request->input('stock_warning');
        $products->stock_warning_type = $request->input('stock_warning_type');
        $products->updated_at = now('GMT+8');
        $products->disabled = $request->input('disabled');
        $products->note = $request->input('note');


        if ($request->input('newStocks') != null) {
            $stockOrder = new StockOrder;
            $stockOrder->product_id = $product->id;
            $stockOrder->stock_reason = $request->input('stock_reason');

            $stockOrder->stock_type = $request->input('newStocks') > 0 ? "Add" : "Reduce";
            $stockOrder->stock = $request->input('newStocks');
            $stockOrder->pack = $request->input('pack');
            $stockOrder->type = $request->input('type');     

            if ($request->input('pack') == 'Pc') {
                $stockOrder->total_stock = floor($products->stock_pc / $products->quantity);
                $products->stock_pc  = $products->stock_pc + $request->input('newStocks');
                $products->stock  = floor($products->stock_pc / $products->quantity);
                $stockOrder->price =$products->price / $products->quantity;
                $stockOrder->total_cost = $request->input('newStocks') * ($products->price / $products->quantity);
            } else {
                $stockOrder->total_stock = $products->stock + $request->input('newStocks');  
                $products->stock = $products->stock + $request->input('newStocks');
                $stockOrder->price = $products->price;
                $stockOrder->total_cost = $request->input('newStocks') * $products->price;
            if ($request->input('quantity') > 1) {
                $wsStocks = $request->input('quantity') * $request->input('newStocks');
                $products->stock_pc = $products->stock_pc + $wsStocks;  
            }
        }

            //  $emails = DB::table('email')
            //             ->where('status', 1)
            //             ->pluck('email')
            //             ->toArray();

            //     $request->mergeIfMissing([
            //         'email_total_cost' => $stockOrder->total_cost,
            //         'email_price' => $stockOrder->price,   
            //         'email_date' => Carbon::now('GMT+8'), 
            //         'emails' => $emails,                 
            //     ]);

            //         Mail::send('modify_stock', ['params' => $request], function ($m) use ($request) {
            //             $m->from(env('MAIL_FROM_ADDRESS'), env('SHOP_NAME'));
            //             $m->to($request->input('emails'))
            //             ->subject('Modified Stock');
            //         });

        $stockOrder->save();
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
