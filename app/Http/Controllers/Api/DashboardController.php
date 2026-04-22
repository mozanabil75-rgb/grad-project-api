<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $recentEnrollments = Enrollment::query()
            ->select(['id', 'user_id', 'course_id', 'grade', 'created_at', 'updated_at'])
            ->with([
                'student:id,first_name,last_name,email,role,created_at,updated_at',
                'course:id,name,description,program_id,created_at,updated_at',
            ])
            ->latest()
            ->limit(5)
            ->get();

        $data = [
            'total_students' => User::query()->where('role', UserRole::Student->value)->count(),
            'total_professors' => User::query()->where('role', UserRole::Professor->value)->count(),
            'total_courses' => Course::query()->count(),
            'total_enrollments' => Enrollment::query()->count(),
            'recent_enrollments' => $recentEnrollments->map(static function (Enrollment $enrollment): array {
                return [
                    'id' => $enrollment->id,
                    'grade' => $enrollment->grade,
                    'created_at' => $enrollment->created_at,
                    'updated_at' => $enrollment->updated_at,
                    'student' => $enrollment->student
                        ? (new UserResource($enrollment->student))->resolve(request())
                        : new \stdClass(),
                    'course' => $enrollment->course
                        ? (new CourseResource($enrollment->course))->resolve(request())
                        : new \stdClass(),
                ];
            }),
        ];

        return ApiResponse::success($data, 'Dashboard data retrieved successfully');
    }
}
