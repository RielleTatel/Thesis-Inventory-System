<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class DepartmentLayout extends Component
{
    /**
     * Authenticated department shell: navy navbar + dark sidebar + content.
     */
    public function render(): View
    {
        return view('layouts.department');
    }
}
