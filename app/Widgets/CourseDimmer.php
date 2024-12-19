<?php

namespace App\Widgets;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Widgets\BaseDimmer;

class CourseDimmer extends BaseDimmer
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $count_courses = Course::all()->count();
        $count_modules = Module::all()->count();
        $count_lessons = Lesson::all()->count();

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-study',
            'title'  => "{$count_courses} Courses, {$count_modules} Modules, {$count_lessons} Lessons",
            'text'   => 'And excellent professors.',
            'button' => [
                'text' => 'View all courses.',
                'link' => route('voyager.courses.index'),
            ],
            'image' => '/courses-dashboard.jpg',
        ]));
    }
}
