<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function index(): JsonResponse
    {
        $departments = Department::query()->orderBy('name')->get();

        return ApiResponse::success(
            DepartmentResource::collection($departments),
            'Departments retrieved successfully.'
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = Department::query()->create($request->validated());

        return ApiResponse::success(
            new DepartmentResource($department),
            'Department created successfully.',
            201
        );
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $department->update($request->validated());

        return ApiResponse::success(
            new DepartmentResource($department->fresh()),
            'Department updated successfully.'
        );
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return ApiResponse::success(null, 'Department deleted successfully.');
    }
}
