<?php

namespace App\Http\Controllers;

use App\Models\OrderSupplierTransaction;
use App\Models\OrderSupplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderSupplierTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.payment_status', 0)
            ->first();

            $total_paid = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_paid'))   
            ->where('ost.payment_status', 1)
            ->first();

          $response = [
              'data' => $data,
              'total_balance' => $total_balance,
              'total_paid' => $total_paid,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }


        public function fetchAllOrderSupplier(Request $request)
    {
         if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {

            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.payment_status', 0)
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

           $total_paid = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_paid'))   
            ->where('ost.payment_status', 1)
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();
         } else {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->where('order_supplier_transaction.order_date', '>=', $request->input('dateFrom'))
            ->where('order_supplier_transaction.order_date', '<=', $request->input('dateTo'))
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
             ->orderBy('order_supplier_transaction.id', 'desc')
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.payment_status', 0)
            ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

           $total_paid = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_paid'))   
            ->where('ost.payment_status', 1)
            ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();            

        }

          $response = [
              'data' => $data,
              'total_balance' => $total_balance,
              'total_paid' => $total_paid,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

         public function fetchPendingPOSupplier(Request $request)
    {
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.status', 'SEND_TO_SUPPLIER')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.status', 'SEND_TO_SUPPLIER')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

        } else {

         $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.order_date', '>=', $request->input('dateFrom'))
            ->where('order_supplier_transaction.order_date', '<=', $request->input('dateTo'))
            ->where('order_supplier_transaction.status', 'SEND_TO_SUPPLIER')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.status', 'SEND_TO_SUPPLIER')
            ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

            
        }

          $response = [
              'data' => $data,
              'total_balance' => $total_balance,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

            public function fetchPendingApproval(Request $request)
    {
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.approval_status', 'PENDING')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.approval_status', 'PENDING')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

        } else {

         $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.order_date', '>=', $request->input('dateFrom'))
            ->where('order_supplier_transaction.order_date', '<=', $request->input('dateTo'))
            ->where('order_supplier_transaction.approval_status', 'PENDING')
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.approval_status', 'PENDING')
            ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

            
        }

          $response = [
              'data' => $data,
              'total_balance' => $total_balance,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

        public function fetchPendingOrderSupplier(Request $request)
    {
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '' ) {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.payment_status', 0)
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.payment_status', 0)
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

        } else {

         $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date','order_supplier_transaction.created_at', 'order_supplier_transaction.updated_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.order_date', '>=', $request->input('dateFrom'))
            ->where('order_supplier_transaction.order_date', '<=', $request->input('dateTo'))
            ->where('order_supplier_transaction.payment_status', 0)
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('order_supplier_transaction.supplier_id', $request->input('supplier_id'));
            })
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.payment_status', 0)
            ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
            ->when($request->filled('supplier_id'), function ($query) use ($request) {
                $query->where('ost.supplier_id', $request->input('supplier_id'));
            })
            ->first();

            
        }

          $response = [
              'data' => $data,
              'total_balance' => $total_balance,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

        public function fetchOrderSupplierByDateV2($date)
    {
        if ($date == 0) {
            $date = date('Y-m-d');
        }

            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status', 'order_supplier_transaction.invoice_number', 'order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'order_supplier_transaction.created_at', 'order_supplier_transaction.send_date',  'order_supplier_transaction.note',
               'order_supplier_transaction.approval', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.requestor', 'supplier.supplier_name',
                'order_supplier_transaction.status', 'order_supplier_transaction.stock_status', 'order_supplier_transaction.updated_at')    
            ->where('order_supplier_transaction.order_date', $date)
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->get();

             foreach ($data as $sotl) {  
                
                $mode_of_payment = DB::table('mode_of_payment_po as mop')
                 ->select('mop.id', 'mop.payment_type_po_id',  'mop.amount', 'mop.order_supplier_transaction_id', 'b.bank_name',
                  'pt.account_name', 'pt.account_number', 'pt.account_description')    
                 ->join('order_supplier_transaction as ost', 'ost.id', '=', 'mop.order_supplier_transaction_id')  
                 ->join('payment_type_po as pt', 'pt.id', '=', 'mop.payment_type_po_id')  
                 ->join('bank as b', 'b.id', '=', 'pt.bank_id')  
                 ->where('pt.id', '!=', 1)
                 ->where('mop.order_supplier_transaction_id', $sotl->id)
                 ->where('ost.order_date', $date)
                 ->get();
                 
                 $sotl->mode_of_payment = $mode_of_payment;
             } 

           $total_balance = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('COUNT(ost.total_transaction_price) as total_count'), DB::raw('SUM(ost.total_transaction_price) as total_balance'))   
            ->where('ost.payment_status', 0)
            ->where('ost.order_date', $date)
            ->first();

          $response = [
              'data' => $data,
              'date' => $date,
              'total_balance' => $total_balance,
              'code' => 200,
              'date' => date('Y-m-d'),
              'message' => "Successfully Added"
          ];

            return response()->json($response);   
    }

        public function fetchOrderSupplierByDate($date)
    {
            $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.payment_status','order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.stock_status')    
            ->orderBy('order_supplier_transaction.id', 'desc')
            ->where('order_supplier_transaction.order_date', $date)  
             ->where('order_supplier_transaction.payment_status', 1)
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
        return view('orderSupplierTransaction.create');
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
            'supplier_id' => 'required',
            'withTax' => 'required',
            'order_date' => 'required'
        ]);

        // $item = UserProfile::create($data);

        // Create Post
        $orderSupplierTransaction = new OrderSupplierTransaction;
        $orderSupplierTransaction->supplier_id = $request->input('supplier_id');
        $orderSupplierTransaction->withTax = $request->input('withTax');
        $orderSupplierTransaction->total_transaction_price = $request->input('total_transaction_price');
        $orderSupplierTransaction->status = $request->input('status');
        $orderSupplierTransaction->requestor = $request->input('requestor');
        $orderSupplierTransaction->payment_status = 0;
        $orderSupplierTransaction->stock_status = 0;
        $orderSupplierTransaction->order_date = $request->input('order_date');
          
        $orderSupplierTransaction->save();
        // return redirect('/categories')->with('success', 'Categories Created');
        return  response()->json($orderSupplierTransaction);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function show(OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);
        //return view('categories.show')->with('category', $category);
        return  response()->json($orderSupplierTransaction);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);
        return response()->json($orderSupplierTransaction);
    }

    public function fetchByOrderSupplierTransactionId($id)
    {
        $data = DB::table('order_supplier_transaction')
            ->join('supplier', 'supplier.id', '=', 'order_supplier_transaction.supplier_id')
            ->select('order_supplier_transaction.id', 'order_supplier_transaction.supplier_id', 'order_supplier_transaction.withTax',  'order_supplier_transaction.total_transaction_price',
             'order_supplier_transaction.order_date', 'order_supplier_transaction.created_at',  'order_supplier_transaction.send_date', 'supplier.supplier_name', 'order_supplier_transaction.status', 'order_supplier_transaction.requestor',
             'order_supplier_transaction.approval', 'order_supplier_transaction.payment_status', 'order_supplier_transaction.checker', 'order_supplier_transaction.receiver', 'order_supplier_transaction.approval_status', 'order_supplier_transaction.note',)    
            ->where('order_supplier_transaction.id', $id)
            ->first();
            return response()->json($data);   
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);

        $total_transaction_price = DB::table('order_supplier')
            ->join('order_supplier_transaction', 'order_supplier_transaction.id', '=', 'order_supplier.order_supplier_transaction_id')
            ->where('order_supplier_transaction.id', $request->id)
            ->sum('order_supplier.total_price');
        
        $orderSupplierTransaction->supplier_id = $request->input('supplier_id');
        $orderSupplierTransaction->withTax = $request->input('withTax');
        $orderSupplierTransaction->total_transaction_price = $total_transaction_price;
        // $orderSupplierTransaction->total_transaction_price = 3000;
        $orderSupplierTransaction->order_date = $request->input('order_date');
        $orderSupplierTransaction->invoice_number = $request->input('invoice_number');
        $orderSupplierTransaction->status = $request->input('status');
        $orderSupplierTransaction->save();
      

        return response()->json($orderSupplierTransaction);
    }

          public function updateDateOrderSupplier(Request $request)
    {
         $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('id'));
         $orderSupplierTransaction->created_at = $request->input('created_at');
         $orderSupplierTransaction->save();
         return response()->json($orderSupplierTransaction);
    }

      public function orderSupplierApproval(Request $request)
    {
         $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('id'));
         $orderSupplierTransaction->approval = $request->input('approval');
         $orderSupplierTransaction->approval_status = $request->input('approval_status');
         $orderSupplierTransaction->note = $request->input('note');
         $orderSupplierTransaction->save();
         return response()->json($orderSupplierTransaction);
    }

    public function updateReceivedOrder($id, Request $request)
    {
        try {
            $result = DB::transaction(function () use ($id, $request) {
                // Serialize calls for this order. This also makes API retries idempotent.
                $orderSupplierTransaction = OrderSupplierTransaction::whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($orderSupplierTransaction->status === 'COMPLETED') {
                    return ['already_completed' => true, 'items' => collect()];
                }

                $items = DB::table('order_supplier')
                    ->select('order_supplier_transaction_id', 'product_id', 'quantity', 'variation')
                    ->where('order_supplier_transaction_id', $id)
                    ->orderBy('id')
                    ->get();

                if ($items->isEmpty()) {
                    throw new \RuntimeException('The supplier order has no items to receive.');
                }

                foreach ($items as $row) {
                    $product = Product::whereKey($row->product_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $unitsPerPack = (int) $product->quantity;
                    $receivedQuantity = (int) $row->quantity;

                    if ($unitsPerPack < 1 || $receivedQuantity < 1) {
                        throw new \RuntimeException(
                            "Invalid quantity for product {$row->product_id}."
                        );
                    }

                    $receivedPieces = $row->variation === 'WHOLESALE'
                        ? $unitsPerPack * $receivedQuantity
                        : $receivedQuantity;

                    // stock_pc is the source of truth; stock is always derived from it.
                    $product->stock_pc = (int) $product->stock_pc + $receivedPieces;
                    $product->stock = intdiv($product->stock_pc, $unitsPerPack);
                    $product->saveOrFail();

                    $updated = OrderSupplier::where(
                            'order_supplier_transaction_id',
                            $row->order_supplier_transaction_id
                        )
                        ->where('product_id', $row->product_id)
                        ->update([
                            'enable' => 1,
                            'stock' => $product->stock,
                            'stock_pc' => $product->stock_pc,
                        ]);

                    if ($updated < 1) {
                        throw new \RuntimeException(
                            "Failed to update supplier order item for product {$row->product_id}."
                        );
                    }
                }

                $orderSupplierTransaction->status = 'COMPLETED';
                $orderSupplierTransaction->checker = $request->input('checker');
                $orderSupplierTransaction->receiver = $request->input('receiver');
                $orderSupplierTransaction->order_date = Carbon::now('GMT+8');
                $orderSupplierTransaction->saveOrFail();

                return ['already_completed' => false, 'items' => $items];
            }, 3);

            return response()->json([
                'code' => 200,
                'message' => $result['already_completed']
                    ? 'Received order was already completed; stock was not added again.'
                    : 'Successfully Updated Received Order',
                'data' => $result['items'],
            ], 200);
        } catch (\Throwable $e) {

            $this->storeAuditTrail($request, $e, 'Update Received Order API', 'exception');

            return response()->json([
                'code' => 500,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function storeAuditTrail(Request $request, \Throwable $exception, $action, $eventType)
    {
        try {
            DB::table('audit_trail')->insert([
                'module' => 'Order Supplier Transaction',
                'action' => $action,
                'event_type' => $eventType,
                'request_method' => $request->method(),
                'endpoint' => $request->fullUrl(),
                'user_id' => optional($request->user())->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'request_payload' => json_encode($request->all()),
                'request_headers' => json_encode($request->headers->all()),
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'exception_file' => $exception->getFile(),
                'exception_line' => $exception->getLine(),
                'stack_trace' => $exception->getTraceAsString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $auditException) {
            Log::error('Failed to store order supplier transaction audit trail', [
                'error' => $auditException->getMessage(),
                'original_error' => $exception->getMessage(),
            ]);
        }
    }
      

        public function setToCompleteTransaction($id)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($id);

            $total_transaction_price = DB::table('order_supplier')
            ->join('order_supplier_transaction', 'order_supplier_transaction.id', '=', 'order_supplier.order_supplier_transaction_id')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.order_supplier_transaction_id', 'order_supplier.product_id', 'order_supplier.quantity', 'order_supplier.variation')
            ->where('order_supplier_transaction.id', $id)
            ->get();

            foreach ($total_transaction_price as $row) { 
                $product = Product::find($row->product_id);

                $initialStock = $product->stock;
                if ($row->variation === 'WHOLESALE') {
                      $product->stock = ($initialStock + $row->quantity);
                        if ($product->quantity > 1) {
                            $newStock = 0;
                            $newStock = $product->quantity * $row->quantity;  
                            $product->stock_pc = $product->stock_pc + $newStock;
                        }
                } else {
                    $newStock = $product->stock_pc + $row->quantity;
                    $product->stock_pc = $newStock;
                    $product->stock = floor($product->stock_pc / $product->quantity);
                }

                OrderSupplier::where('order_supplier_transaction_id', $row->order_supplier_transaction_id)
                ->where('product_id', $row->product_id)
                ->update(['enable' => 1, 'stock' => $product->stock, 'stock_pc' => $product->stock_pc]);

                $product->save();
            }

        $orderSupplierTransaction->status = 'COMPLETED';
        $orderSupplierTransaction->order_date = Carbon::now('GMT+8');
        $orderSupplierTransaction->save();      

        return response()->json($total_transaction_price);
    }

        public function setToCompletePaymentTransaction($id)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($id);
        $orderSupplierTransaction->payment_status = 1;
        $orderSupplierTransaction->save();
        return response()->json($orderSupplierTransaction);
    }

      public function setSendtoSupplierStatus(Request $request)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($request->input('id'));
        $orderSupplierTransaction->status = $request->input('status');
        $orderSupplierTransaction->send_date = $request->input('send_date');
        $orderSupplierTransaction->save();
        return response()->json($orderSupplierTransaction);
    }



 public function setToCancelTransaction($id)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($id);

            $total_transaction_price = DB::table('order_supplier')
            ->join('order_supplier_transaction', 'order_supplier_transaction.id', '=', 'order_supplier.order_supplier_transaction_id')
            ->join('products', 'products.id', '=', 'order_supplier.product_id')
            ->select('order_supplier.product_id', 'order_supplier.quantity', 'order_supplier.variation')
            ->where('order_supplier_transaction.id', $id)
            ->get();
            $result = false;
            $message = "";

           foreach ($total_transaction_price as $row) { 
                $product = Product::find($row->product_id);
                $initialStock = $product->stock;
                if ($row->variation === 'WHOLESALE') {
                    if ($row->quantity > $product->stock)
                    {
                         $result = true; 
                         $message = $product->product_name;
                         break;
                    }
                } else {
                    if ($row->quantity > $product->stock_pc)
                    {
                         $result = true; 
                         $message = $product->product_name;
                         break;
                    }                    
                }
            }


            if (!$result) {
                foreach ($total_transaction_price as $row) { 
                    $product = Product::find($row->product_id);
                    $initialStock = $product->stock;
                    if ($row->variation === 'WHOLESALE') {
                        $product->stock = ($initialStock - $row->quantity);
                            if ($product->quantity > 0) {
                                $newStock = 0;
                                $newStock = $product->quantity * $row->quantity;  
                                $product->stock_pc = $product->stock_pc - $newStock;
                            }
                    } else {
                        $newStock = $product->stock_pc - $row->quantity;
                        $product->stock_pc = $newStock;
                        $product->stock = (int)($product->stock_pc / $product->quantity);
                    }

                    $product->save();
                }
          $response = [
              'id' => $id,
              'code' => 200,
              'message' => "Successfully Added"
          ];
             $orderSupplierTransaction->status = 'CANCELLED';
            //  $orderSupplierTransaction->save();
             $orderSupplierTransaction->delete();
        } else {
           $response = [
              'id' => $id,
              'code' => 500,
              'message' => "Unable to Cancel, not Enough stock of ".$message
          ];
        }
        return response()->json($response);
    }
           public function fetchOrderSupplierReport(Request $request)
    {
      
        if ( $request->input('dateFrom') == '' &&  $request->input('dateTo') == '') {
            $shop_order_transaction_list = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('SUM(ost.total_transaction_price) as total_sales') , DB::raw('COUNT(ost.id) as total_count'),
             DB::raw('ost.order_date as date'))  
            ->join('mode_of_payment_po as mop', 'mop.order_supplier_transaction_id', '=', 'ost.id')  
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_po_id')  
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')  
            // ->where('shop.shop_type_id', 3)
             ->where('ost.payment_status', 1)
             ->orderBy('ost.id', 'DESC')
             ->groupBy('ost.order_date')
            ->get();



            $payment_type = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('COUNT(mop.id) as total_count'), 
            'ptp.account_name', 'ptp.account_number', 'ptp.account_description', 'ptp.id', 'b.bank_name')  
            ->join('mode_of_payment_po as mop', 'mop.order_supplier_transaction_id', '=', 'ost.id')  
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_po_id')  
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')  
             ->where('ost.payment_status', 1)
             ->orderBy('ost.id', 'DESC')
             ->groupBy('ptp.id')
            ->get();

        } else {

            $shop_order_transaction_list = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('SUM(ost.total_transaction_price) as total_sales'), DB::raw('COUNT(ost.id) as total_count'),
             DB::raw('ost.order_date as date'))  
            ->join('mode_of_payment_po as mop', 'mop.order_supplier_transaction_id', '=', 'ost.id')  
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_po_id')  
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')  
            // ->where('shop.shop_type_id', 3)
             ->where('ost.payment_status', 1)
            ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
             ->orderBy('ost.id', 'DESC')
             ->groupBy('ost.order_date')
            ->get();

            $payment_type = DB::table('order_supplier_transaction as ost')
            ->select(DB::raw('SUM(mop.amount) as total_amount'), DB::raw('COUNT(mop.id) as total_count'), 
            'ptp.account_name', 'ptp.account_number', 'ptp.account_description', 'ptp.id', 'b.bank_name')  
            ->join('mode_of_payment_po as mop', 'mop.order_supplier_transaction_id', '=', 'ost.id')  
            ->join('payment_type_po as ptp', 'ptp.id', '=', 'mop.payment_type_po_id')  
            ->join('bank as b', 'b.id', '=', 'ptp.bank_id')  
             ->where('ost.order_date', '>=', $request->input('dateFrom'))
            ->where('ost.order_date', '<=', $request->input('dateTo'))
             ->where('ost.payment_status', 1)
             ->orderBy('ost.id', 'DESC')
             ->groupBy('ost.order_date')
            ->get();

        }  

        

            $total_sales = 0;
            foreach ($shop_order_transaction_list as $datavals) {  
                $total_sales += $datavals->total_sales;
            }

           $response = [
              'data' => $shop_order_transaction_list,
              'payment' => $payment_type,
              'code' => 200,
              'total_sales' => $total_sales,
              'message' => "Successfully Added"
          ];


            return response()->json($response);   
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OrderSupplierTransaction  $orderSupplierTransaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderSupplierTransaction $orderSupplierTransaction)
    {
        $orderSupplierTransaction = OrderSupplierTransaction::find($orderSupplierTransaction->id);
        $orderSupplierTransaction->delete();

        //  $orderSupplier = OrderSupplier::find($orderSupplier->id);
        // $orderSupplier->delete();
        return response()->json($orderSupplierTransaction);
    }
}
