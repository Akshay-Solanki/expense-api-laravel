<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{


    /**
     * Get transaction
     *
     * get current user's trasactions
     *
     * @param Request $request 
     * @return JsonResponse
     * @throws exception
     **/
    public function index(Request $request): JsonResponse
    {
        $transactions = $request->user()->transactions()->orderBy('date', 'desc')->get();

        return response()->json([
            'message' => 'success',
            'result'    => [
                'transactions' => $transactions
            ]
        ]);
    }


    /**
     * Transaction create
     *
     * Create new trasanction current user
     *
     * @param Request $request 
     * @return JsonResponse
     * @throws exception
     **/
    public function store(Request $request): JsonResponse
    {
        $category = $request->user()->categories()->where('ulid', $request->category_ulid)->first();
        \DB::beginTransaction();
        if (array_search($category->type, ['INCOME', 'EXPENSE'])) {
            $request->validate([
                'category_ulid' => ['required', 'string', Rule::exists('categories', 'ulid')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
                'account_ulid'  => ['required', 'string', Rule::exists('accounts', 'ulid')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
                'date' => ['required', 'date'],
                'amount'    => ['required', 'numeric'],
                'title'    => ['required', 'string'],
                'description'    => ['nullable', 'string'],
            ]);
            $account = $request->user()->accounts()->where('ulid', $request->account_ulid)->first();
            if ($category->type == 'EXPENSE') {
                if ($account->balance < $request->amount) {
                    return response()->json([
                        'message' => 'Low balance in account',
                        'result'  => []
                    ], 400);
                }
                $account->update([
                    'balance' => $account->balance - $request->amount
                ]);
            } else if ($category->type == 'INCOME') {
                $account->update([
                    'balance' => $account->balance + $request->amount
                ]);
            }
        } else {
            $request->validate([
                'category_ulid' => ['required', 'string', Rule::exists('categories', 'ulid')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
                'account_ulid'  => ['required', 'string', Rule::exists('accounts', 'ulid')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
                'to_account_ulid'  => ['required', 'string', Rule::exists('accounts', 'ulid')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
                'date' => ['required', 'date'],
                'amount'    => ['required', 'numeric'],
                'title'    => ['required', 'string'],
                'description'    => ['nullable', 'string'],
            ]);

            $from_account = $request->user()->accounts()->where('ulid', $request->account_ulid)->first();
            $to_account = $request->user()->accounts()->where('ulid', $request->to_account_ulid)->first();

            $from_account_amount = $from_account->balance - $request->amount;
            $to_account_amount = $to_account->balance + $request->amount;

            $from_account->update([
                'balance' => $from_account_amount,
            ]);

            $to_account->update([
                'balance' => $to_account_amount,
            ]);
        }

        $data = array_filter($request->only('category_ulid', 'account_ulid', 'to_account_ulid', 'date', 'amount', 'title', 'description'));
        $transaction = $request->user()->transactions()->create($data);

        \DB::commit();

        return response()->json([
            'message' => 'Transaction created successfully',
            'result'  => [
                'transaction' => $transaction
            ]
        ]);
    }

    /**
     * Transaction Update
     *
     * Update current user transaction
     *
     * @param Request $request
     * @param string $ulid
     * @return JsonResponse
     * @throws exception
     **/
    public function update(Request $request, string $ulid): JsonResponse
    {
        $request->validate([
            'category_ulid' => ['required', 'string', Rule::exists('categories')
                ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
            'account_ulid'  => ['required', 'string', Rule::exists('accounts')
                ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))],
            'date' => ['required', 'date'],
            'amount'    => ['required', 'numeric'],
            'title'    => ['required', 'string'],
            'description'    => ['nullable', 'string'],
        ]);

        $transaction = $request->user()->transactions()->where('ulid', $ulid)->first();

        tap($transaction)->update($request->only('category_ulid', 'account_ulid', 'date', 'title', 'amount', 'description'));

        return response()->json([
            'message' => 'Transaction updated successfully',
            'result'  => [
                'transaction' => $transaction
            ]
        ]);
    }

    /**
     * Delete Transaction
     *
     * Delete transaction of current user
     *
     * @param Request $request
     * @param string $ulid
     * @return JsonResponse
     * @throws exception
     **/
    public function destroy(Request $request, string $ulid)
    {
        if ($request->user()->transactions()->where('ulid', $ulid)->delete()) {
            return response()->json([
                'message' => 'Transaction deleted successfully',
                'result'  => []
            ]);
        }
        return response()->json([
            'message' => 'Transaction not found',
            'result'  => []
        ], 404);
    }
}
