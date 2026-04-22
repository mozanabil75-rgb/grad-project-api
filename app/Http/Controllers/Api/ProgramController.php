<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Program\StoreProgramRequest;
use App\Http\Requests\Program\UpdateProgramRequest;
use App\Http\Resources\ProgramResource;
use App\Models\Program;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    public function index(): JsonResponse
    {
        $programs = Program::query()
            ->with('department')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            ProgramResource::collection($programs),
            'Programs retrieved successfully.'
        );
    }

    public function store(StoreProgramRequest $request): JsonResponse
    {
        $program = Program::query()->create($request->validated());

        $program->load('department');

        return ApiResponse::success(
            new ProgramResource($program),
            'Program created successfully.',
            201
        );
    }

    public function update(UpdateProgramRequest $request, Program $program): JsonResponse
    {
        $program->update($request->validated());
        $program->load('department');

        return ApiResponse::success(
            new ProgramResource($program->fresh()),
            'Program updated successfully.'
        );
    }

    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return ApiResponse::success(null, 'Program deleted successfully.');
    }
}
