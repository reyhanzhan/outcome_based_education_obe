<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Cpmk_Cpl_Mk_Controller extends Controller
{

    public function index()
    {
        $cpls = Cpl::with(['cpmks.mks'])
                    ->whereHas('cpmks')
                    ->whereHas('mksThroughCpmk')
                    ->get();

        return view('pemetaan_CPMK-CPL-MK.index', compact('cpls'));
    }

    
}
