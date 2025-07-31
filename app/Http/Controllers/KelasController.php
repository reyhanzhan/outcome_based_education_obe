<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\KelasImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->role, ['kps'])) {
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

        $tahunFilter = request('tahun', session('selected_year', ''));
        if ($tahunFilter) {
            session(['selected_year' => $tahunFilter]);
        } else if (!session('selected_year')) {
            session(['selected_year' => '']); // Default ke "Semua Tahun" jika belum ada
        }
        Log::info("Index - kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");

        // Ambil daftar tahun unik dari kelas untuk dropdown
        $availableYears = Kelas::where('kode_prodi', $kodeProdi)
            ->distinct()
            ->pluck('tahun')
            ->sortDesc()
            ->values();

        // Query kelas berdasarkan filter tahun
        $query = Kelas::where('kode_prodi', $kodeProdi)->with('user', 'mk');
        if ($tahunFilter) {
            $query->where('tahun', $tahunFilter);
        } else {
            // Jika tidak ada tahunFilter, tetap menampilkan data tanpa filter (opsional, bisa diubah)
            Log::info("No tahunFilter set, showing all years for kode_prodi: {$kodeProdi}");
        }
        $kelas = $query->get();

        Log::info('Fetched kelas data count: ' . $kelas->count() . ', Data: ', $kelas->toArray());

        return view('kelas.index', compact('kelas', 'availableYears', 'tahunFilter'));
    } catch (\Exception $e) {
        Log::error('Error fetching kelas: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data kelas: ' . $e->getMessage());
    }
}

    public function create()
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (is_null($kodeProdi)) {
                Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
            }

            $matkul = Mk::where('kode_prodi', $kodeProdi)->get();
            $users = \App\Models\User::where('kode_prodi', $kodeProdi)->where('role', 'dosen')->get();
            return view('kelas.create', compact('matkul', 'users'));
        } catch (\Exception $e) {
            Log::error('Error fetching data for create kelas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (is_null($kodeProdi)) {
                Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
            }

            $request->validate([
                'tahun' => 'required|date_format:Y',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'periode' => 'required',
                'nip_dosen' => 'required|exists:users,nip',
            ]);

            $data = [
                'kode_prodi' => $kodeProdi,
                'tahun' => $request->tahun,
                'kode_mk' => $request->kode_mk,
                'periode' => $request->periode,
                'nip_dosen' => $request->nip_dosen,
            ];

            $kelas = Kelas::create($data);

            if (!$kelas) {
                throw new \Exception('Gagal menyimpan data kelas.');
            }

            return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error storing kelas: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing kelas: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data kelas: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (is_null($kodeProdi)) {
                Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
            }

            $kelas = Kelas::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $matkul = Mk::where('kode_prodi', $kodeProdi)->get();
            $users = \App\Models\User::where('kode_prodi', $kodeProdi)->where('role', 'dosen')->get();
            return view('kelas.edit', compact('kelas', 'matkul', 'users'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Kelas not found with id ' . $id . ': ' . $e->getMessage());
            return redirect()->route('kelas.index')->with('error', 'Data kelas tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error fetching kelas for edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data kelas: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (is_null($kodeProdi)) {
                Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
            }

            $request->validate([
                'tahun' => 'required|date_format:Y',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'periode' => 'required',
                'nip_dosen' => 'required|exists:users,nip',
            ]);

            $kelas = Kelas::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $kelas->update([
                'tahun' => $request->tahun,
                'kode_mk' => $request->kode_mk,
                'periode' => $request->periode,
                'nip_dosen' => $request->nip_dosen,
            ]);

            return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating kelas: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Kelas not found with id ' . $id . ': ' . $e->getMessage());
            return redirect()->route('kelas.index')->with('error', 'Data kelas tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error updating kelas: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data kelas: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (is_null($kodeProdi)) {
                Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
            }

            Log::info('Attempting to delete kelas with id: ' . $id . ', kode_prodi: ' . $kodeProdi);

            $kelas = Kelas::where('kode_prodi', $kodeProdi)->find($id);
            if (!$kelas) {
                Log::error('Kelas not found with id ' . $id . ' and kode_prodi ' . $kodeProdi);
                return redirect()->route('kelas.index')->with('error', 'Data kelas dengan ID ' . $id . ' tidak ditemukan untuk kode prodi Anda.');
            }

            $kelas->delete();
            Log::info('Kelas deleted successfully with id: ' . $id);

            return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting kelas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data kelas: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
{
    try {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
        ]);

        if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
            return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('kelas.template') . '">Download template</a>.');
        }

        $file = $request->file('file');
        $kodeProdi = Auth::user()->kode_prodi;

        $tahunFilter = request('tahun', session('selected_year', ''));
        if (!$tahunFilter) {
            Log::error("Tahun tidak ditemukan di session atau request untuk kode_prodi {$kodeProdi}.");
            return redirect()->back()->with('error', 'Tahun kurikulum tidak ditemukan. Pilih tahun terlebih dahulu.');
        }

        // Tambahkan logging sebelum impor
        Log::info("Starting import for kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");

        Excel::import(new KelasImport($kodeProdi, $tahunFilter), $file);

        Log::info("Import completed for kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");
        return redirect()->route('kelas.index', ['tahun' => $tahunFilter])->with('success', 'Data kelas berhasil diimpor.');
    } catch (\Exception $e) {
        Log::error('Error importing kelas: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
    }
}

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'tahun');
        $sheet->setCellValue('B1', 'kode_mk');
        $sheet->setCellValue('C1', 'periode');
        $sheet->setCellValue('D1', 'nip_dosen');

        $sheet->setCellValueExplicit('A2', '2025', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', '00A001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '2024', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D2', '0724067103', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'kelas_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_kelas.xlsx')->deleteFileAfterSend(true);
    }
}