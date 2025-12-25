<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mail;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shop = Shop::all();
        return response()->json($shop);
    }

    public function fetchShopList()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description')
          ->get();
        return response()->json($data);   
    }

    

        public function fetchShopActive()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description' , 'shop.status', 'shop.address', 'shop.contact_number')
          ->where('shop.status', 1)  
          ->get();
        return response()->json($data);  
    }

       public function fetchCurrentShop()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description' , 'shop.status', 'shop.address', 'shop.contact_number')
          ->where('shop.status', 1)  
          ->first();
        return response()->json($data);  
    }

    public function fetchShopCurrent()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description' , 'shop.status', 'shop.address', 'shop.contact_number')
          ->where('shop.status', 1)  
          ->first();
        return response()->json($data);  
    }

        public function fetcOnlineShopList()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description')
          ->where('shop_type.id', 2)  
          ->get();
        return response()->json($data);   
    }

      public function fetchPhysicalStoreList()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description')
          ->whereIn('shop_type.id', [1,2])    
          ->get();
        return response()->json($data);   
    }

      public function fetchOnlineOrderList()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description')
          ->where('shop_type.id', 3)  
          ->get();
        return response()->json($data);   
    }

     public function test()
    {
        $data = DB::table('shop')
          ->join('shop_type', 'shop.shop_type_id', '=', 'shop_type.id')
          ->select('shop.id', 'shop.shop_name','shop.shop_type_id',
            'shop_type.shop_type_description')
          ->where('shop_type.id', 3)  
          ->first();
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
            'shop_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $shop = new Shop;
        $shop->shop_name = $request->input('shop_name');
        $shop->shop_type_id = $request->input('shop_type_id');;
        $shop->save();
        return  response()->json($shop);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop)
    {
        $shop = Shop::find($shop->id);
        return  response()->json($shop);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function edit(Shop $shop)
    {
        $shop = Shop::find($shop->id);
        return response()->json($shop);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Shop $shop)
    {
        $shop = Shop::find($warehouse->id);
        
        $shop->shop_name = $request->input('shop_name');
        $shop->shop_type_id = $request->input('shop_type_id');;
        $shop->save();
      
        return response()->json($shop);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
      {
          $shop = Shop::find($shop->id);
          $shop->delete();
          return response()->json($shop);
      }


     public function sendReport(Request $request) {
        $data = array('name'=>"Virat Gandhi");
    

           Mail::send('mail', ['params' => $request], function ($m) use ($request) {
            $m->from('caloocan@mdrbakingsupplies.com', $request->input('shop_name'));
            // $m->to('cedchuaa0324@gmail.com')
            $m->to($request->input('emails'))
          //  ->cc(['manalolady88@gmail.com', 'cedchua123@yahoo.com'])
              ->subject("Sales Report");
          });

      return response()->json($request);
    }

  public function html_email() {
      $data = array('name'=>"Virat Gandhi");
      Mail::send('mail', $data, function($message) {
         $message->to('cedchua123@yahoo.com', 'Tutorials Point')->subject
            ('Laravel HTML Testing Mail');
         $message->from('cedchuaa0324@gmail.com','Virat Gandhi');
      });
      echo "HTML Email Sent. Check your inbox.";
   }
   public function attachment_email() {
      $data = array('name'=>"Virat Gandhi");
      Mail::send('mail', $data, function($message) {
         $message->to('cedchua123@yahoo.com', 'Tutorials Point')->subject
            ('Laravel Testing Mail with Attachment');
         $message->attach('C:\laravel-master\laravel\public\uploads\image.png');
         $message->attach('C:\laravel-master\laravel\public\uploads\test.txt');
         $message->from('cedchuaa0324@gmail.com','Virat Gandhi');
      });
      echo "Email Sent with attachment. Check your inbox.";
   }

}
