<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Mk;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KrsImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KrsController extends Controller
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
        if (is_null($kodeProdi)) {
            Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
            return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
        }

        $tahunFilter = request('tahun', session('selected_year'));

        // Simpan tahun yang dipilih ke session
        if (request()->has('tahun')) {
            session(['selected_year' => request('tahun')]);
        } elseif (!session('selected_year')) {
            session(['selected_year' => '']); // Default ke "Semua Tahun" jika belum ada
        }

        // Ambil daftar tahun unik dari KRS untuk dropdown
        $availableYears = Krs::where('kode_prodi', $kodeProdi)
            ->distinct()
            ->pluck('tahun')
            ->sortDesc()
            ->values();

        // Query KRS berdasarkan filter tahun
        $query = Krs::where('kode_prodi', $kodeProdi)->with('mahasiswa', 'mk');
        if ($tahunFilter) {
            $query->where('tahun', $tahunFilter);
        }
        $krs = $query->get();

        return view('krs.index', compact('krs', 'availableYears'));
    } catch (\Exception $e) {
        Log::error('Error fetching KRS: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data KRS.');
    }
}

    public function create()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini.');
        }

        $mahasiswa = Mahasiswa::where('kode_prodi', $kodeProdi)->get();
        $matkul = Mk::where('kode_prodi', $kodeProdi)->get();
        $kelas = Kelas::where('kode_prodi', $kodeProdi)->get();
        return view('krs.create', compact('kodeProdi', 'mahasiswa', 'matkul', 'kelas'));
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
                'periode' => 'required',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'tahun' => 'required|date_format:Y',
                'nama_kelas' => 'required',
                'nim' => 'required|exists:mahasiswa,nim',
            ]);

            Krs::create([
                'periode' => $request->periode,
                'kode_prodi' => $kodeProdi,
                'kode_mk' => $request->kode_mk,
                'tahun' => $request->tahun,
                'nama_kelas' => $request->nama_kelas,
                'nim' => $request->nim,
            ]);

            return redirect()->route('krs.index')->with('success', 'KRS berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error storing KRS: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing KRS: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data KRS: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $krs = Krs::findOrFail($id);
            if ($krs->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data KRS ini.');
            }
            $mahasiswa = Mahasiswa::where('kode_prodi', Auth::user()->kode_prodi)->get();
            $matkul = Mk::where('kode_prodi', Auth::user()->kode_prodi)->get();
            $kelas = Kelas::where('kode_prodi', Auth::user()->kode_prodi)->get();
            return view('krs.edit', compact('krs', 'mahasiswa', 'matkul', 'kelas'));
        } catch (\Exception $e) {
            Log::error('Error fetching KRS for edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data KRS.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $krs = Krs::findOrFail($id);
            if ($krs->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data KRS ini.');
            }

            Log::info('Request data received for update: ', $request->all());

            $request->validate([
                'periode' => 'required',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'tahun' => 'required|date_format:Y',
                'nama_kelas' => 'required',
                'nim' => 'required|exists:mahasiswa,nim',
            ]);

            $data = [
                'periode' => $request->periode,
                'kode_prodi' => $krs->kode_prodi, // Gunakan kode_prodi yang sudah ada
                'kode_mk' => $request->kode_mk,
                'tahun' => $request->tahun,
                'nama_kelas' => $request->nama_kelas,
                'nim' => $request->nim,
            ];

            $updated = $krs->update($data);
            if (!$updated) {
                Log::warning('Update failed for KRS ID ' . $id . ' with data: ', $data);
                return redirect()->back()->with('error', 'Gagal memperbarui data KRS.');
            }

            Log::info('KRS updated successfully with ID: ' . $id);
            return redirect()->route('krs.index')->with('success', 'KRS berhasil diperbarui!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating KRS: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('KRS not found with id ' . $id . ': ' . $e->getMessage());
            return redirect()->route('krs.index')->with('error', 'Data KRS tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error updating KRS: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->route('krs.index')->with('error', 'Terjadi kesalahan saat memperbarui data KRS: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $krs = Krs::findOrFail($id);
            if ($krs->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data KRS ini.');
            }
            $krs->delete();

            return redirect()->route('krs.index')->with('success', 'KRS berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting KRS: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data KRS.');
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
            return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('krs.template') . '">Download template</a>.');
        }

        $file = $request->file('file');
        $kodeProdi = Auth::user()->kode_prodi;

        // Ambil tahunFilter dari session atau request
        $tahunFilter = request('tahun', session('selected_year', ''));
        if (!$tahunFilter) {
            Log::error("Tahun tidak ditemukan di session atau request untuk kode_prodi {$kodeProdi}.");
            return redirect()->back()->with('error', 'Tahun kurikulum tidak ditemukan. Pilih tahun terlebih dahulu.');
        }

        Log::info("Starting import for kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}, file: {$file->getClientOriginalName()}");

        // Impor file dengan tahunFilter
        Excel::import(new KrsImport($kodeProdi, $tahunFilter), $file);

        Log::info("Import completed for kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");
        return redirect()->route('krs.index', ['tahun' => $tahunFilter])->with('success', 'Data KRS berhasil diimpor.');
    } catch (\Exception $e) {
        Log::error('Error importing KRS: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
    }
}

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'periode');
        $sheet->setCellValue('B1', 'kode_mk');
        $sheet->setCellValue('C1', 'tahun');
        $sheet->setCellValue('D1', 'nama_kelas');
        $sheet->setCellValue('E1', 'nim');

        // Set contoh data sebagai teks
        $sheet->setCellValueExplicit('A2', '2024/2025 Ganjil', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', 'MK001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '2025', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D2', 'Kelas A', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E2', '1234567890', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set header style
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'krs_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_krs.xlsx')->deleteFileAfterSend(true);
    }
}