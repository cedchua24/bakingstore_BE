<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ProductExcelExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\DailySessionController;
use App\Models\DailySession;

class DashBoardController extends Controller
{
   public function submitStartOfDay(Request $request)
    {
        DailySession::where('date', '=',  $request->input('today'))->update(['status' => 1]);
        $dailySessionController = new DailySessionController();
        $dailySessionController->store($request);


        //  return response()->json($request);  
        return Excel::download(new ProductExcelExport, 'product_reports_' . $request->input('today') . '.xlsx');      
    }
}
