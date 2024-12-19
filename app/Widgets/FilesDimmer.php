<?php

namespace App\Widgets;

use App\Models\File;
use TCG\Voyager\Widgets\BaseDimmer;

class FilesDimmer extends BaseDimmer
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
        $count_forums = File::all()->count();


        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-categories',
            'title'  => "{$count_forums} Files",
            'text'   => 'For consult and study',
            'button' => [
                'text' => 'View files.',
                'link' => route('voyager.files.index'),
            ],
            'image' => '/files-dashboard.jpg',
        ]));
    }
}
