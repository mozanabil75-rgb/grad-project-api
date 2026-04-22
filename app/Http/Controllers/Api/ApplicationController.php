<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreApplicationRequest;
use App\Http\Requests\Application\UpdateApplicationStatusRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isProfessor()) {
            return ApiResponse::error('You are not allowed to list applications.', 403);
        }

        $query = Application::query()->with(['user', 'program.department']);

        if ($user->isStudent()) {
            $query->where('user_id', $user->id);
        }

        $applications = $query->orderByDesc('created_at')->get();

        return ApiResponse::success(
            ApplicationResource::collection($applications),
            'Applications retrieved successfully.'
        );
    }

    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $user = $request->user();
        $programId = (int) $request->validated('program_id');

        if (Application::query()->where('user_id', $user->id)->where('program_id', $programId)->exists()) {
            return ApiResponse::error(
                'You have already applied to this program.',
                422,
                ['program_id' => ['You have already applied to this program.']]
            );
        }

        $application = Application::query()->create([
            'user_id' => $user->id,
            'program_id' => $programId,
            'status' => ApplicationStatus::Pending,
        ]);

        $application->load(['user', 'program.department']);

        return ApiResponse::success(
            new ApplicationResource($application),
            'Application submitted successfully.',
            201
        );
    }

    public function updateStatus(UpdateApplicationStatusRequest $request, Application $application): JsonResponse
    {
        $application->update([
            'status' => $request->validated('status'),
        ]);

        $application->load(['user', 'program.department']);

        return ApiResponse::success(
            new ApplicationResource($application->fresh()),
            'Application status updated successfully.'
        );
    }
}
