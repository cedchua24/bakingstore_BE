<?php

namespace App\Http\Controllers;

use App\Models\PrintingTransactionComment;
use Illuminate\Http\Request;

class PrintingTransactionCommentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'printing_transaction_id' => 'sometimes|integer|exists:printing_transaction,id',
        ]);

        return response()->json(PrintingTransactionComment::with('user:id,name')
            ->where($filters)->orderBy('id')->get());
    }

    public function store(Request $request)
    {
        $comment = PrintingTransactionComment::create($request->validate($this->rules()));

        return response()->json($comment->load('user:id,name'), 201);
    }

    public function show(PrintingTransactionComment $printingTransactionComment)
    {
        return response()->json($printingTransactionComment->load('user:id,name'));
    }

    public function update(Request $request, PrintingTransactionComment $printingTransactionComment)
    {
        $printingTransactionComment->update($request->validate($this->rules(true)));

        return response()->json($printingTransactionComment->refresh()->load('user:id,name'));
    }

    public function destroy(PrintingTransactionComment $printingTransactionComment)
    {
        $printingTransactionComment->delete();

        return response()->noContent();
    }

    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';

        return [
            'printing_transaction_id' => $required.'|integer|exists:printing_transaction,id',
            'user_id' => $required.'|integer|exists:users,id',
            'comment' => $required.'|string',
        ];
    }
}
