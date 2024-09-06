<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpensesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $expenses = Expenses::all();
        // // return view('categories.index')->with('categories', $categories);
        // return response()->json($expenses);

        $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name', 'ec.expenses_category_name')
            ->where('e.date', date('Y-m-d'))    
            ->get();
            return response()->json($data);   
    }

    public function fetchExpensesMandatoryToday($date)
      {
          $currentTime = date('Y-m-d');
            $newDate = '';
            if ($date == 0 || $date === "undefined") {
                $newDate = $currentTime;
            } else {
                $newDate =$date;
            }          

         $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name', 'ec.expenses_category_name')
            ->where('e.date', $newDate)    
             ->where('ec.id', 1) 
            ->get();


            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'), DB::raw('e.date'),  DB::raw('e.id'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->orderBy('e.id', 'DESC')
            ->groupBy('e.date')
            ->where('e.date', $newDate)  
            ->where('ec.id', 1)    
            ->get();


            
            $total_expenses = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_expenses += $datavals->total_expenses;
            }
           $response = [
              'data' => $data,
              'code' => 200,
              'total_expenses' => $total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
      }

          public function fetchExpensesNonMandatoryToday($date)
      {

         $currentTime = date('Y-m-d');
            $newDate = '';
             if ($date == 0 || $date === "undefined") {
                $newDate = $currentTime;
            } else {
                $newDate =$date;
            }       

         $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name', 'ec.expenses_category_name')
            ->where('e.date', $newDate)    
             ->where('ec.id', 2) 
            ->get();


            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'), DB::raw('e.date'),  DB::raw('e.id'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->orderBy('e.id', 'DESC')
            ->groupBy('e.date')
            ->where('e.date', $newDate)  
            ->where('ec.id', 2)    
            ->get();


            
            $total_expenses = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_expenses += $datavals->total_expenses;
            }
           $response = [
              'data' => $data,
              'code' => 200,
              'total_expenses' => $total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
      }
      


  public function fetchExpensesByDate($date)
    {
            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'), DB::raw('e.date'),  DB::raw('e.id'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->orderBy('e.id', 'DESC')
            ->groupBy('e.date')
            ->where('e.date', $date)    
            ->get();


            $total_expenses = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_expenses += $datavals->total_expenses;
            }
           $response = [
              'data' => $expenses_transaction_list,
              'code' => 200,
              'total_expenses' => $total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

  public function fetchExpensesTransactionToday()
    {
            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'), DB::raw('e.date'),  DB::raw('e.id'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->orderBy('e.id', 'DESC')
            ->groupBy('e.date')
            ->where('e.date', date('Y-m-d'))    
            ->get();


            $total_expenses = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_expenses += $datavals->total_expenses;
            }
           $response = [
              'data' => $expenses_transaction_list,
              'code' => 200,
              'total_expenses' => $total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

               public function fetchExpensesTransaction()
    {
            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'), DB::raw('e.date'),  DB::raw('e.id'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->orderBy('e.id', 'DESC')
            ->groupBy('e.date')
            ->get();


            $total_expenses = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_expenses += $datavals->total_expenses;
            }
           $response = [
              'data' => $expenses_transaction_list,
              'code' => 200,
              'total_expenses' => $total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

  public function fetchExpensesTransactionByDate(Request $request)
    {
            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'), DB::raw('e.date'),  DB::raw('e.id'))  
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->where('e.date', '>=', $request->input('dateFrom'))
            ->where('e.date', '<=', $request->input('dateTo'))
            ->orderBy('e.id', 'DESC')
            ->groupBy('e.date')
            ->get();


            $total_expenses = 0;
            foreach ($expenses_transaction_list as $datavals) {    
                $total_expenses += $datavals->total_expenses;
            }
           $response = [
              'data' => $expenses_transaction_list,
              'code' => 200,
              'total_expenses' => $total_expenses,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

        public function fetchExpensesTransactionById($date)
    {
        $currentTime = date('Y-m-d');
        $newDate = '';
        if ($date == 0) {
            $newDate = $currentTime;
        } else {
            $newDate =$date;
        }


        $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name', 'ec.expenses_category_name')
               ->where('e.date', $newDate)    
            ->get();


            $expenses_transaction_list = DB::table('expenses as e')
            ->select(DB::raw('SUM(e.amount) as total_expenses'))  
             ->where('e.date', $newDate)    
            ->first();

           $response = [
              'data' => $data,
              'code' => 200,
              'expenses' => $expenses_transaction_list,
              'message' => "Successfully Added"
          ];


            return response()->json($response);    
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
            'expenses_type_id' => 'required'
        ]);

        // Create Post
        $expenses = new Expenses;
        $expenses->expenses_type_id = $request->input('expenses_type_id');
        $expenses->details = $request->input('details');
        $expenses->amount = $request->input('amount');    
        $expenses->date = $request->input('date');   
        $expenses->save();
        return  response()->json($expenses);
    }

        public function fetchExpenseById($id)
    {
    //    $data = Expenses::find($id);

        $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name', 'ec.expenses_category_name', 'ep.expenses_name', 'ec.id as category_id')
               ->where('e.id', $id)    
            ->first();

        //return view('categories.show')->with('category', $category);
        return  response()->json($data);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function show(Expenses $expenses)
    {
        // $data = Expenses::find($expenses->id);
        //return view('categories.show')->with('category', $category);

               $data = DB::table('expenses as e')
            ->join('expenses_type as ep', 'ep.id', '=', 'e.expenses_type_id')
            ->join('expenses_category as ec', 'ec.id', '=', 'ep.expenses_category_id')
            ->select('e.id', 'e.details',  'e.amount', 'e.date',
              'ep.expenses_name', 'ec.expenses_category_name')
               ->where('e.id', $expenses->id)    
            ->get();
        return  response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function edit(Expenses $expenses)
    {
        $brands = Expenses::find($expenses->id);
        
        //return view('categories.show')->with('category', $category);
        return  response()->json($brands);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Expenses $expenses)
    {
        $expenses = Expenses::find($request->input('id'));
        $expenses->expenses_type_id = $request->input('expenses_type_id');
        $expenses->details = $request->input('details');
        $expenses->amount = $request->input('amount');    
        $expenses->date = $request->input('date');  
        $expenses->date = $request->input('date');    
        $expenses->save();
        return  response()->json($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expenses  $expenses
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        $expenses = Expenses::find($id);
        $expenses->delete();
        return response()->json($expenses);
    }
}
