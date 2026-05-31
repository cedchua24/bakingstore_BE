<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ProductExcelExport;
use App\Exports\PriceListExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\DailySessionController;
use App\Services\CheckListTransactionService;
use App\Models\DailySession;

class DashBoardController extends Controller
{
    public function submitStartOfDay(Request $request, CheckListTransactionService $checkListTransactionService)
    {
        $existingDailySession = DailySession::where('date', $request->input('today'))
            ->exists();

        DailySession::where('date', $request->input('today'))
            ->update(['status' => 1]);

        $dailySessionController = new DailySessionController();
        $dailySessionController->store($request);

        if (!$existingDailySession) {
            $checkListTransactionService->createStartOfDayTransactions(
                $request->input('today')
            );
        }

        return Excel::download(new ProductExcelExport, 'product_reports_' . $request->input('today') . '.xlsx');
    }

        public function submitExportPriceList(Request $request)
    {

        return Excel::download(new PriceListExport, 'price_list_' . $request->input('today') . '.xlsx');
    }
}
