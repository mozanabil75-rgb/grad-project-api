<?php

namespace App\Services;

use App\Models\Course;

class CourseProfessorSync
{
    public function sync(Course $course, array $professorIds): void
    {
        $course->professors()->sync($professorIds);
    }
}
