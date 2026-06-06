<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Authenticated admin shell: navy navbar + dark sidebar (admin menu) + content.
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
