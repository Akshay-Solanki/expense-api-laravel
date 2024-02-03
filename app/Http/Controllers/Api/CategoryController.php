<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'success',
            'result' => Category::all()
        ]);
    }


    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => [
                'required', 'string', 'min:2',
                Rule::unique('categories')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))
            ],
            'type' => 'required|in:INCOME,EXPENSE,TRANSFER'
        ]);

        $category = $request->user()->categories()->create($request->only('name', 'type'));

        return response()->json([
            'message' => 'Category created successfully',
            'result' => [
                'category' => $category
            ]
        ]);
    }

    public function show(Request $request, string $ulid): JsonResponse
    {

        if ($category = $request->user()->categories()->where('ulid', $ulid)->first()) {
            return response()->json([
                'message' => 'Category found',
                'result' => [
                    'category' => $category
                ]

            ]);
        }
        return response()->json([
            'message' => 'Category not found',
            'result' => []
        ], 404);
    }

    public function update(Request $request, string $ulid): JsonResponse
    {
        $request->validate([
            'name' => [
                'required', 'string', 'min:2',
                Rule::unique('categories')
                    ->where(fn (Builder $query) => $query->where('user_ulid', $request->user()->ulid))
                    ->ignore($ulid, 'ulid')
            ],
        ]);

        $category = $request->user()->categories()
            ->where('ulid', $ulid)->first();

        tap($category)->update($request->only('name'));

        return response()->json([
            'message' => 'Category updated successfully',
            'result'  => [
                'category' => $category
            ]
        ]);
    }


    public function destroy(Request $request, string $ulid): JsonResponse
    {

        if ($request->user()->categories()->where('ulid', $ulid)->delete()) {
            return response()->json([
                'message' => 'Category delete successfully',
                'result'  => []
            ]);
        }
        return response()->json([
            'message' => 'Category not found',
            'result'  => []
        ], 404);
    }
}
