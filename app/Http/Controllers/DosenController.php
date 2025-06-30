<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DosenImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Validator;

class DosenController extends Controller
{
    public function index()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $dosens = User::where('role', 'dosen')->where('kode_prodi', $kodeProdi)->get();
        return view('dosen.index', compact('dosens'));
    }

    public function create()
    {
        return view('dosen.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip',
            'password' => 'required|string',
            'kode_prodi' => 'required|string',
        ]);

        User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
            'role' => 'dosen',
            'kode_prodi' => Auth::user()->kode_prodi, // Tetap menggunakan kode_prodi KPS
        ]);

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $dosen = User::where('id', $id)->where('kode_prodi', Auth::user()->kode_prodi)->firstOrFail();
        return response()->json($dosen);
    }

    public function update(Request $request, $id)
    {
        $dosen = User::where('id', $id)->where('kode_prodi', Auth::user()->kode_prodi)->firstOrFail();
        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|unique:users,nip,' . $id,
            'kode_prodi' => 'required|string',
        ]);

        $dosen->update([
            'name' => $request->name,
            'nip' => $request->nip,
            'kode_prodi' => Auth::user()->kode_prodi, // Tetap menggunakan kode_prodi KPS
        ]);

        if ($request->filled('password')) {
            $dosen->password = Hash::make($request->password);
            $dosen->save();
        }

        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $dosen = User::where('id', $id)->where('kode_prodi', Auth::user()->kode_prodi)->firstOrFail();
        $dosen->delete();
        return redirect()->route('dosen.index')->with('success', 'Dosen berhasil dihapus.');
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
            ]);

            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('dosen.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            Excel::import(new DosenImport($kodeProdi), $file);

            return redirect()->route('dosen.index')->with('success', 'Data dosen berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'name');
        $sheet->setCellValue('B1', 'nip');
        $sheet->setCellValue('C1', 'password');

        $sheet->setCellValueExplicit('A2', 'Dosen 1', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', '201114101', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', 'password123', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'dosen_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_dosen.xlsx')->deleteFileAfterSend(true);
    }
}