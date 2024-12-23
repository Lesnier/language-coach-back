<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::all();

        return response()->json($courses);
    }

    public function show($id, Request $request)
    {
        $course = Course::findOrFail($id)
            ->load('modules.lessons');

        return response()->json($course);
    }
}
