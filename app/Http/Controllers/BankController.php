<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

           $data = DB::table('bank')
            ->select( 'id', 'bank_name', 'status')
            ->where('id', '!=', 1)    
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
            'bank_name' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $bank = new Bank;
        $bank->bank_name = $request->input('bank_name');
        $bank->status = $request->input('status');
        $bank->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($bank);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function show(Bank $bank)
    {
        $banks = Bank::find($bank->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($banks);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function edit(Bank $bank)
    {
        $banks = Bank::find($bank->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($banks);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bank $bank)
    {
        $banks = Bank::find($bank->id);
        $banks->bank_name = $request->input('bank_name');
        $banks->status = $request->input('status');
        $banks->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($banks);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Bank  $bank
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bank $bank)
    {
        $bank = Bank::find($bank->id);
        $bank->delete();
        return response()->json($bank);
    }
}
