<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Imports\MkImport;

class MkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'kps') {
                abort(403, 'Akses hanya untuk KPS.');
            }
            return $next($request);
        });
    }

    public function index()
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $mk = Mk::where('kode_prodi', $kodeProdi)->get();
            return view('MK.index', compact('mk'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('MK.create');
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $request->validate([
                'kode_mk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    }),
                ],
                'deskripsi' => 'required|string',
                'sks' => 'required|integer|min:1',
                'jenis_mk' => 'required|string', // Hapus in:Kuliah,Skripsi
            ], [
                'kode_mk.unique' => 'Kode MK sudah digunakan untuk prodi ini.',
                'kode_mk.required' => 'Kode MK wajib diisi.',
                'kode_mk.max' => 'Kode MK terlalu panjang (maksimal 255 karakter).',
                'deskripsi.required' => 'Deskripsi wajib diisi.',
                'sks.required' => 'SKS wajib diisi.',
                'sks.integer' => 'SKS harus berupa angka bulat.',
                'sks.min' => 'SKS minimal 1.',
                'jenis_mk.required' => 'Jenis Mata Kuliah wajib diisi.',
            ]);

            $data = [
                'kode_mk' => $request->kode_mk,
                'deskripsi' => $request->deskripsi,
                'sks' => $request->sks,
                'jenis_mk' => $request->jenis_mk,
                'kode_prodi' => $kodeProdi,
            ];

            $mk = Mk::create($data);

            if (!$mk) {
                throw new \Exception('Gagal menyimpan data MK.');
            }

            return redirect()->route('mk.index')->with([
                'success' => 'Data MK berhasil ditambahkan!',
                'new_id' => $mk->id,
            ]);
        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $mk = Mk::where('kode_prodi', $kodeProdi)->findOrFail($id);
            return view('MK.edit', compact('mk'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $mk = Mk::where('kode_prodi', $kodeProdi)->findOrFail($id);

            $request->validate([
                'kode_mk' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('mk')->where(function ($query) use ($kodeProdi) {
                        return $query->where('kode_prodi', $kodeProdi);
                    })->ignore($mk->id),
                ],
                'deskripsi' => 'required|string',
                'sks' => 'required|integer|min:1',
                'jenis_mk' => 'required|string', // Hapus in:Kuliah,Skripsi
            ], [
                'kode_mk.unique' => 'Kode MK sudah digunakan untuk prodi ini.',
                'kode_mk.required' => 'Kode MK wajib diisi.',
                'kode_mk.max' => 'Kode MK terlalu panjang (maksimal 255 karakter).',
                'deskripsi.required' => 'Deskripsi wajib diisi.',
                'sks.required' => 'SKS wajib diisi.',
                'sks.integer' => 'SKS harus berupa angka bulat.',
                'sks.min' => 'SKS minimal 1.',
                'jenis_mk.required' => 'Jenis Mata Kuliah wajib diisi.',
            ]);

            $data = [
                'kode_mk' => $request->kode_mk,
                'deskripsi' => $request->deskripsi,
                'sks' => $request->sks,
                'jenis_mk' => $request->jenis_mk,
                'kode_prodi' => $kodeProdi,
            ];

            

            $mk->update($data);

            return redirect()->route('mk.index')->with('success', 'Data MK berhasil diperbarui');
        } catch (\Exception $e) {
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            $mk = Mk::where('kode_prodi', $kodeProdi)->findOrFail($id);
           
            $mk->delete();

            return redirect()->route('mk.index')->with('success', 'Data MK berhasil dihapus');
        } catch (\Exception $e) {
           
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header
            $sheet->setCellValue('A1', 'Kode MK');
            $sheet->setCellValue('B1', 'Deskripsi');
            $sheet->setCellValue('C1', 'SKS');
            $sheet->setCellValue('D1', 'Jenis Mata Kuliah');

            // Set header style
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Auto-size columns
            foreach (range('A', 'D') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Simpan file sementara
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'mk_template');
            $writer->save($tempFile);

            return response()->download($tempFile, 'template_mk.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
           
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh template: ' . $e->getMessage());
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
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('mk.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

          

            // Impor file menggunakan MkImport
            Excel::import(new MkImport($kodeProdi), $file);

            return redirect()->route('mk.index')->with('success', 'Data Mata Kuliah berhasil diimpor. Periksa log untuk detail perubahan.');
        } catch (\Exception $e) {
           
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}