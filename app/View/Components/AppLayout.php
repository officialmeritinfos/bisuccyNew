<?php

namespace App\View\Components;

use Illuminate\View\Component;

class AppLayout extends Component
{
    /**
     * This layout is used for app's internal routes such as dashboard.
     * Get the view / contents that represents the component.
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('layouts.app');
    }
}
