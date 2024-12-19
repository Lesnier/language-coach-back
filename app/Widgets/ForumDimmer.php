<?php

namespace App\Widgets;

use App\Models\Forum;
use TCG\Voyager\Widgets\BaseDimmer;

class ForumDimmer extends BaseDimmer
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
        $count_forums = Forum::all()->count();


        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-group',
            'title'  => "{$count_forums} Forums",
            'text'   => 'To share knowledge',
            'button' => [
                'text' => 'View forums.',
                'link' => route('voyager.forums.index'),
            ],
            'image' => '/forum-dashboard.jpg',
        ]));
    }
}
