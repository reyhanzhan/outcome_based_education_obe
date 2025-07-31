<?php

namespace App\Http\Controllers;

use App\Models\Kurikulum;
use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KurikulumImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KurikulumController extends Controller
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
            $tahunFilter = request('tahun', session('selected_year'));

            // Simpan tahun yang dipilih ke session
            if (request()->has('tahun')) {
                session(['selected_year' => request('tahun')]);
            } elseif (!session('selected_year')) {
                session(['selected_year' => '']); // Default ke "Semua Tahun" jika belum ada
            }

            // Ambil daftar tahun unik dari kurikulum untuk dropdown
            $availableYears = Kurikulum::where('kode_prodi', $kodeProdi)
                ->distinct()
                ->pluck('tahun')
                ->sortDesc()
                ->values();

            // Query kurikulum berdasarkan filter tahun
            $query = Kurikulum::where('kode_prodi', $kodeProdi)->with('mk');
            if ($tahunFilter) {
                $query->where('tahun', $tahunFilter);
            }
            $kurikulum = $query->get();

            return view('kurikulum.index', compact('kurikulum', 'availableYears'));
        } catch (\Exception $e) {
            Log::error('Error fetching kurikulum: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data kurikulum.');
        }
    }

    public function create()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini.');
        }

        $matkul = Mk::where('kode_prodi', $kodeProdi)->get();
        return view('kurikulum.create', compact('kodeProdi', 'matkul'));
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini.');
            }

            Log::info('Request data received: ', $request->all());

            $request->validate([
                'tahun' => 'required|date_format:Y',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'semester' => 'nullable',
            ]);

            Kurikulum::create([
                'tahun' => $request->tahun,
                'kode_prodi' => $kodeProdi,
                'kode_mk' => $request->kode_mk,
                'semester' => $request->semester,
            ]);

            return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error storing kurikulum: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing kurikulum: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data kurikulum: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $kurikulum = Kurikulum::findOrFail($id);
            if ($kurikulum->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data kurikulum ini.');
            }
            $matkul = Mk::where('kode_prodi', Auth::user()->kode_prodi)->get();
            return view('kurikulum.edit', compact('kurikulum', 'matkul'));
        } catch (\Exception $e) {
            Log::error('Error fetching kurikulum for edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data kurikulum.');
        }
    }

    public function update(Request $request, $id)
{
    try {
        $kurikulum = Kurikulum::findOrFail($id);
        if ($kurikulum->kode_prodi !== Auth::user()->kode_prodi) {
            abort(403, 'Anda tidak memiliki akses ke data kurikulum ini.');
        }

        Log::info('Request data received for update: ', $request->all());

        $request->validate([
            'tahun' => 'required|date_format:Y',
            'kode_mk' => 'required|exists:mk,kode_mk',
            'semester' => 'nullable|integer',
        ]);

        $data = [
            'tahun' => $request->tahun,
            'kode_prodi' => $kurikulum->kode_prodi, // Gunakan kode_prodi yang sudah ada, bukan ulang set
            'kode_mk' => $request->kode_mk,
            'semester' => $request->semester,
        ];

        $updated = $kurikulum->update($data);
        if (!$updated) {
            Log::warning('Update failed for kurikulum ID ' . $id . ' with data: ', $data);
            return redirect()->back()->with('error', 'Gagal memperbarui data kurikulum.');
        }

        Log::info('Kurikulum updated successfully with ID: ' . $id);
        return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil diperbarui!');
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error updating kurikulum: ' . $e->getMessage());
        return redirect()->back()->withErrors($e->validator)->withInput();
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Kurikulum not found with id ' . $id . ': ' . $e->getMessage());
        return redirect()->route('kurikulum.index')->with('error', 'Data kurikulum tidak ditemukan.');
    } catch (\Exception $e) {
        Log::error('Error updating kurikulum: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        return redirect()->route('kurikulum.index')->with('error', 'Terjadi kesalahan saat memperbarui data kurikulum: ' . $e->getMessage())->withInput();
    }
}

    public function destroy($id)
    {
        try {
            $kurikulum = Kurikulum::findOrFail($id);
            if ($kurikulum->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data kurikulum ini.');
            }
            $kurikulum->delete();

            return redirect()->route('kurikulum.index')->with('success', 'Kurikulum berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting kurikulum: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data kurikulum.');
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
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('kurikulum.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            // Impor file menggunakan KurikulumImport
            Excel::import(new KurikulumImport($kodeProdi), $file);

            return redirect()->route('kurikulum.index')->with('success', 'Data kurikulum berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Error importing kurikulum: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'tahun');
        $sheet->setCellValue('B1', 'kode_mk');
        $sheet->setCellValue('C1', 'semester');

        // Set contoh data sebagai teks
        $sheet->setCellValueExplicit('A2', '2025', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', 'MK001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '1', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set header style
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'kurikulum_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_kurikulum.xlsx')->deleteFileAfterSend(true);
    }
}