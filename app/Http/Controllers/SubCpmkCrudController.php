<?php

namespace App\Http\Controllers;

use App\Models\SubCpmk;
use App\Models\Cpmk;
use Illuminate\Http\Request;

class SubCpmkCrudController extends Controller
{
    public function index()
    {
        $subcpmks = SubCpmk::with('cpmk')->paginate(10);
        return view('Sub_Cpmk.index', compact('subcpmks'));
    }

    public function create()
    {
        $cpmks = Cpmk::all();
        return view('Sub_Cpmk.create', compact('cpmks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cpmk_id' => 'required',
            'kode_subcpmk' => 'required|unique:subcpmk,kode_subcpmk',
            'uraian' => 'required',
        ]);

        SubCpmk::create($request->all());
        return redirect()->route('subcpmk.index')->with('success', 'Sub-CPMK berhasil ditambahkan.');
    }

    public function edit(SubCpmk $subcpmk)
    {
        $cpmks = Cpmk::all();
        return view('Sub_Cpmk.edit', compact('subcpmk', 'cpmks'));
    }

    public function update(Request $request, SubCpmk $subcpmk)
    {
        $request->validate([
            'cpmk_id' => 'required',
            'kode_subcpmk' => 'required|unique:subcpmk,kode_subcpmk,' . $subcpmk->id,
            'uraian' => 'required',
        ]);

        $subcpmk->update($request->all());
        return redirect()->route('subcpmk.index')->with('success', 'Sub-CPMK berhasil diperbarui.');
    }

    public function destroy(SubCpmk $subcpmk)
    {
        $subcpmk->delete();
        return redirect()->route('subcpmk.index')->with('success', 'Sub-CPMK berhasil dihapus.');
    }
}
