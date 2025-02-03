<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Cpmk_Cpl_Mk_Controller extends Controller
{
    public function index()
    {
        // Ambil CPL yang memiliki relasi ke CPMK dan MK
        $cpls = Cpl::whereHas('cpmks')->whereHas('mks')
            ->with(['cpmks', 'mks']) // Ambil juga CPMK dan MK yang terkait
            ->get();

        return view('pemetaan_CPMK-CPL-MK.index', compact('cpls'));
    }
}
