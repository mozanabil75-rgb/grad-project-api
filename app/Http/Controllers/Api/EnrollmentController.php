<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Enrollment\StoreEnrollmentRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Enrollment::query()->with(['user', 'course.program']);

        if ($user->isStudent()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isProfessor()) {
            $query->whereHas('course', function ($q) use ($user): void {
                $q->whereHas('professors', function ($q2) use ($user): void {
                    $q2->where('users.id', $user->id);
                });
            });
        }

        $enrollments = $query->orderByDesc('created_at')->get();

        return ApiResponse::success(
            EnrollmentResource::collection($enrollments),
            'Enrollments retrieved successfully.'
        );
    }

    public function store(StoreEnrollmentRequest $request): JsonResponse
    {
        $response = $this->performEnrollment($request);

        if ($response['status'] !== 201) {
            return ApiResponse::error($response['message'], $response['status']);
        }

        return ApiResponse::success(
            new EnrollmentResource($response['enrollment']),
            $response['message'],
            $response['status']
        );
    }

    public function enroll(StoreEnrollmentRequest $request): JsonResponse
    {
        $response = $this->performEnrollment($request);

        if ($response['status'] !== 201) {
            return ApiResponse::error($response['message'], $response['status']);
        }

        return ApiResponse::success(
            new CourseResource($response['course']),
            $response['message'],
            $response['status']
        );
    }

    private function performEnrollment(StoreEnrollmentRequest $request): array
    {
        $user = $request->user();
        $courseId = (int) $request->validated('course_id');
        $result = $user->courses()->syncWithoutDetaching([$courseId]);

        if ($result['attached'] === []) {
            return [
                'message' => 'You are already enrolled in this course.',
                'status' => 409,
            ];
        }

        $enrollment = Enrollment::query()
            ->with(['user', 'course.program'])
            ->where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $course = $user->courses()
            ->with(['program.department', 'professors'])
            ->where('courses.id', $courseId)
            ->firstOrFail();

        return [
            'message' => 'Enrolled successfully.',
            'status' => 201,
            'enrollment' => $enrollment,
            'course' => $course,
        ];
    }
}
