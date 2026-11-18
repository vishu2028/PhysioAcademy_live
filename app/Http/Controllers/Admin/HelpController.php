<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HelpController extends Controller
{
    public function subscription(): View
    {
        return view('admin.help.subscription');
    }
}
