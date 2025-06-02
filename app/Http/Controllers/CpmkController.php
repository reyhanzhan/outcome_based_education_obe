<?php

namespace App\Http\Controllers;

use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Imports\CpmkImport;

class CpmkController extends Controller
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
        $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->get();
        return view('CPMK.index', compact('cpmk'));
    }

    public function create()
    {
        return view('CPMK.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_cpmk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('cpmk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
            ]);

            $data = [
                'kode_cpmk' => $request->kode_cpmk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ];

            $cpmk = Cpmk::create($data);

            if (!$cpmk) {
                throw new \Exception('Gagal menyimpan data CPMK.');
            }

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('CPMK.edit', compact('cpmk'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_cpmk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('cpmk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($cpmk->id),
                ],
                'deskripsi' => 'required|string',
            ]);

            $cpmk->update([
                'kode_cpmk' => $request->kode_cpmk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $cpmk = Cpmk::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $cpmk->delete();

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Kode CPMK');
        $sheet->setCellValue('B1', 'Deskripsi');

        // Set header style
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'B') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'cpmk_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_cpmk.xlsx')->deleteFileAfterSend(true);
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
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('cpmk.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            // Impor file menggunakan CpmkImport
            Excel::import(new CpmkImport($kodeProdi), $file);

            return redirect()->route('cpmk.index')->with('success', 'Data CPMK berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}