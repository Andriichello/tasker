<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use Illuminate\View\View;

class WebController extends BaseController
{
    /**
     * Returns web application's view.
     *
     * @return View
     */
    public function view(): View
    {
        return view('app');
    }
}
