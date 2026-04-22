<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professor\StoreProfessorRequest;
use App\Http\Requests\Professor\UpdateProfessorRequest;
use App\Http\Resources\ProfessorResource;
use App\Models\Professor;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ProfessorController extends Controller
{
    public function index(): JsonResponse
    {
        $professors = User::query()
            ->where('role', UserRole::Professor)
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            $professors->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ])->values()->all(),
            'Professors retrieved successfully.'
        );
    }

    public function store(StoreProfessorRequest $request): JsonResponse
    {
        $professor = Professor::query()->create($request->validated());
        $professor->load('department');

        return ApiResponse::success(
            new ProfessorResource($professor),
            'Professor created successfully.',
            201
        );
    }

    public function show(Professor $professor): JsonResponse
    {
        $professor->load('department');

        return ApiResponse::success(
            new ProfessorResource($professor),
            'Professor retrieved successfully.'
        );
    }

    public function update(UpdateProfessorRequest $request, Professor $professor): JsonResponse
    {
        $professor->update($request->validated());
        $professor->load('department');

        return ApiResponse::success(
            new ProfessorResource($professor->fresh()),
            'Professor updated successfully.'
        );
    }

    public function destroy(Professor $professor): JsonResponse
    {
        $professor->delete();

        return ApiResponse::success(null, 'Professor deleted successfully.');
    }
}
