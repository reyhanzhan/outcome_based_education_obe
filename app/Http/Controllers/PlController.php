<?php

namespace App\Http\Controllers;

use App\Models\Pl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Imports\PlImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;


class PlController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Pastikan hanya user yang login yang bisa akses
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'kps') {
                abort(403, 'Akses hanya untuk KPS.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $pls = Pl::where('kode_prodi', $kodeProdi)->get();
        return view('PL.index', compact('pls'));
    }

    public function create()
    {
        return view('PL.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_pl' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('pl')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
                'kategori' => 'required|string',
            ]);

            $data = [
                'kode_pl' => $request->kode_pl,
                'deskripsi' => $request->deskripsi,
                'kategori' => $request->kategori,
                'kode_prodi' => $kodeProdi,
            ];

            $pl = Pl::create($data);

            if (!$pl) {
                throw new \Exception('Gagal menyimpan data PL.');
            }

            return redirect()->route('pl.index')->with('success', 'Data PL berhasil ditambahkan.');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $pl = Pl::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('PL.edit', compact('pl'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $pl = Pl::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_pl' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('pl')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($pl->id),
                ],
                'deskripsi' => 'required|string',
                'kategori' => 'required|string',
            ]);

            $pl->update([
                'kode_pl' => $request->kode_pl,
                'deskripsi' => $request->deskripsi,
                'kategori' => $request->kategori,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('pl.index')->with('success', 'Data PL berhasil diperbarui');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $pl = Pl::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $pl->delete();

            return redirect()->route('pl.index')->with('success', 'Data PL berhasil dihapus');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
{
    try {
        // Validasi file
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
        ]);

        // Periksa apakah file ada dan valid
        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('pl.template') . '">Download template</a>.');
        }

        $file = $request->file('file');
        $kodeProdi = Auth::user()->kode_prodi;

        // Impor file menggunakan PlImport
        Excel::import(new PlImport($kodeProdi), $file);

        return redirect()->route('pl.index')->with('success', 'Data Profil Lulusan berhasil diimpor.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
}

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Kode PL');
        $sheet->setCellValue('B1', 'Deskripsi');
        $sheet->setCellValue('C1', 'Kategori');

        // Set contoh data (opsional)
        // $sheet->setCellValue('A2', 'PL01');
        // $sheet->setCellValue('B2', 'Lulusan mampu menguasai keahlian teknis');
        // $sheet->setCellValue('C2', 'Keterampilan');

        // Set header style (opsional)
        $sheet->getStyle('A1:C1')->getFont()->setBold(true); // Ubah D1 jadi C1
        $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'C') as $columnID) { // Ubah D jadi C
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'pl_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_pl.xlsx')->deleteFileAfterSend(true);
    }

    public function bulkDestroy(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:pls,id'
    ]);

    try {
        Pl::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => 'Data terpilih berhasil dihapus.']);
    } catch (\Exception $e) {
        Log::error('Bulk destroy failed: ' . $e->getMessage());
        return response()->json(['error' => 'Terjadi kesalahan saat menghapus data.'], 500);
    }
}


}