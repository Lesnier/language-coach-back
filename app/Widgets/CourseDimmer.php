<?php

namespace App\Widgets;

use App\Models\Course;
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
        $count = Course::all()->count();

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-study',
            'title'  => "{$count} Courses",
            'text'   => 'We have ' . $count . ' courses.',
            'button' => [
                'text' => 'View Courses.',
                'link' => route('voyager.courses.index'),
            ],
            'image' => '/courses-dashboard.jpg',
        ]));
    }
}
