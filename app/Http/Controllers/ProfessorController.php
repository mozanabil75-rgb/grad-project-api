<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Professor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfessorController extends Controller
{
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            Professor::all()->toArray(),
            'Professors retrieved successfully.'
        );
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:professors',
            'title' => 'nullable',
        ]);

        $professor = Professor::create($request->all());

        return ApiResponse::success(
            $professor->toArray(),
            'Professor created successfully.',
            201
        );
    }

    public function show(Professor $professor): JsonResponse
    {
        return ApiResponse::success(
            $professor->toArray(),
            'Professor retrieved successfully.'
        );
    }

    public function update(Request $request, Professor $professor): JsonResponse
    {
        $professor->update($request->all());

        return ApiResponse::success(
            $professor->fresh()->toArray(),
            'Professor updated successfully.'
        );
    }

    public function destroy(Professor $professor): JsonResponse
    {
        $professor->delete();

        return ApiResponse::success(null, 'Professor deleted successfully.');
    }
}
