<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Course\AssignProfessorRequest;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Services\CourseProfessorSync;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseProfessorSync $courseProfessorSync
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = max(1, min((int) $request->query('per_page', 10), 100));

        $nameFilter = trim((string) $request->query('name', ''));
        if ($nameFilter === '' && $request->filled('search')) {
            $nameFilter = trim((string) $request->query('search'));
        }

        $programId = $request->query('program_id');

        $query = Course::query()->with(['program', 'professors']);

        if ($user->isProfessor()) {
            $query->whereHas('professors', function ($q) use ($user): void {
                $q->where('users.id', $user->id);
            });
        }

        if ($programId !== null && $programId !== '') {
            $query->where('program_id', (int) $programId);
        }

        if ($nameFilter !== '') {
            $query->where('name', 'like', '%'.$nameFilter.'%');
        }

        $paginatedCourses = $query
            ->orderBy('name')
            ->paginate($perPage)
            ->appends($request->query());

        return ApiResponse::success([
            'items' => CourseResource::collection($paginatedCourses->getCollection()),
            'meta' => [
                'current_page' => $paginatedCourses->currentPage(),
                'last_page' => $paginatedCourses->lastPage(),
                'per_page' => $paginatedCourses->perPage(),
                'total' => $paginatedCourses->total(),
                'from' => $paginatedCourses->firstItem(),
                'to' => $paginatedCourses->lastItem(),
            ],
        ], 'Courses retrieved successfully.');
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $professorIds = $data['professor_ids'] ?? [];
        unset($data['professor_ids']);

        $course = Course::query()->create($data);

        if ($professorIds !== []) {
            $this->courseProfessorSync->sync($course, $professorIds);
        }

        $course->load(['program.department', 'professors']);

        return ApiResponse::success(
            new CourseResource($course),
            'Course created successfully.',
            201
        );
    }

    public function update(UpdateCourseRequest $request, Course $course): JsonResponse
    {
        $syncProfessors = $request->has('professor_ids');
        $data = $request->validated();

        if ($syncProfessors) {
            $professorIds = $data['professor_ids'] ?? [];
            unset($data['professor_ids']);
            $this->courseProfessorSync->sync($course, $professorIds);
        }

        if ($data !== []) {
            $course->update($data);
        }

        $course->load(['program.department', 'professors']);

        return ApiResponse::success(
            new CourseResource($course->fresh()),
            'Course updated successfully.'
        );
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return ApiResponse::success(null, 'Course deleted successfully.');
    }

    public function assignProfessor(AssignProfessorRequest $request, Course $course): JsonResponse
    {
        $professorId = (int) $request->validated('professor_id');
        $result = $course->professors()->syncWithoutDetaching([$professorId]);

        $course->load(['program.department', 'professors']);

        return ApiResponse::success(
            new CourseResource($course),
            $result['attached'] === []
                ? 'Professor is already assigned to this course.'
                : 'Professor assigned successfully.'
        );
    }
}
