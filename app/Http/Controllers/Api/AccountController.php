<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'success',
            'result' => Account::all()
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required', 'string', 'min:2',
                Rule::unique('accounts')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))
            ],
            'balance' => [
                'required', 'numeric'
            ]
        ]);

        $account = $request->user()->accounts()->create($request->only('name', 'balance'));

        return response()->json([
            'message' => 'Account created successfully',
            'result' => [
                'account' => $account
            ]
        ]);
    }

    public function show(Request $request, string $ulid): JsonResponse
    {

        if ($account = $request->user()->accounts()->where('ulid', $ulid)->first()) {
            return response()->json([
                'message' => 'Account found',
                'result' => [
                    'account' => $account
                ]

            ]);
        }
        return response()->json([
            'message' => 'Account not found',
            'result' => []
        ], 404);
    }

    public function update(Request $request, string $ulid): JsonResponse
    {
        $request->validate([
            'name' => [
                'required', 'string', 'min:2',
                Rule::unique('accounts')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))
                    ->ignore($ulid, 'ulid')
            ],
            'balance' => [
                'numeric'
            ]
        ]);

        $account = $request->user()->accounts()
            ->where('ulid', $ulid)->first();

        tap($account)->update(array_filter($request->only('name', 'balance')));

        return response()->json([
            'message' => 'Account updated successfully',
            'result'  => [
                'account' => $account
            ]
        ]);
    }


    public function destroy(Request $request, string $ulid): JsonResponse
    {

        if ($request->user()->accounts()->where('ulid', $ulid)->delete()) {
            return response()->json([
                'message' => 'Account deleted successfully',
                'result'  => []
            ]);
        }
        return response()->json([
            'message' => 'Account not found',
            'result'  => []
        ], 404);
    }
}
