<?php

namespace App\Http\Controllers;

use App\Models\Bk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Imports\BkImport;
use Illuminate\Routing\Controller;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Validator;

class BkController extends Controller
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
        $bk = Bk::where('kode_prodi', $kodeProdi)->get();
        return view('BK.index', compact('bk'));
    }

    public function create()
    {
        return view('BK.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_bk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('bk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
            ]);

            $data = [
                'kode_bk' => $request->kode_bk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ];

            $bk = Bk::create($data);

            if (!$bk) {
                throw new \Exception('Gagal menyimpan data BK.');
            }

            return redirect()->route('bk.index')->with('success', 'Data BK berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $bk = Bk::where('kode_prodi', $kodeProdi)->findOrFail($id);
        return view('BK.edit', compact('bk'));
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $bk = Bk::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_bk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('bk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($bk->id),
                ],
                'deskripsi' => 'required|string',
            ]);

            $bk->update([
                'kode_bk' => $request->kode_bk,
                'deskripsi' => $request->deskripsi,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('bk.index')->with('success', 'Data BK berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $bk = Bk::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $bk->delete();

            return redirect()->route('bk.index')->with('success', 'Data BK berhasil dihapus');
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
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('bk.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            // Impor file menggunakan BkImport
            Excel::import(new BkImport($kodeProdi), $file);

            return redirect()->route('bk.index')->with('success', 'Data Bahan Kajian berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Kode BK');
        $sheet->setCellValue('B1', 'Deskripsi'); // Ubah dari 'Nama BK' ke 'Deskripsi'

        // Set header style
        $sheet->getStyle('A1:B1')->getFont()->setBold(true);
        $sheet->getStyle('A1:B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'B') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'bk_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_bk.xlsx')->deleteFileAfterSend(true);
    }
}