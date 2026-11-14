<?php

namespace App\Http\Controllers;

use App\Services\DatabaseExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseExportController extends Controller
{
    public function __invoke(DatabaseExportService $exporter): BinaryFileResponse|JsonResponse
    {
        try {
            $export = $exporter->export();

            return response()
                ->download($export['path'], $export['filename'], [
                    'Content-Type' => 'application/sql',
                    'Cache-Control' => 'no-store, private',
                ])
                ->deleteFileAfterSend(true);
        } catch (\Throwable $exception) {
            Log::error('Database export failed.', [
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'Unable to export the database.',
            ], 500);
        }
    }
}
