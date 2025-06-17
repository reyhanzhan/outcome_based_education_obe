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

            $kelas = Kelas::where('kode_prodi', $kodeProdi)->with('user', 'mk')->get();
            Log::info('Fetched kelas data count: ' . $kelas->count() . ', Data: ', $kelas->toArray());

            // Tambahkan data dummy jika kosong
            if ($kelas->isEmpty()) {
                $kelas = collect([
                    (object) ['id' => 1, 'tahun_kurikulum' => '2025', 'kode_mk' => 'MK001', 'periode' => '20251', 'nip_dosen' => '201114101', 'user' => (object) ['name' => 'Dosen 1']],
                    (object) ['id' => 2, 'tahun_kurikulum' => '2024', 'kode_mk' => 'MK002', 'periode' => '20241', 'nip_dosen' => '201114102', 'user' => (object) ['name' => 'Dosen 2']]
                ]);
            }

            // Gunakan map untuk mengisi nilai default jika null, bukan menghapus properti
            $kelas = $kelas->map(function ($item) {
                return (object) [
                    'id' => $item->id ?? null,
                    'tahun_kurikulum' => $item->tahun_kurikulum ?? 'N/A',
                    'kode_mk' => $item->kode_mk ?? 'N/A',
                    'periode' => $item->periode ?? 'N/A',
                    'nip_dosen' => $item->nip_dosen ?? 'N/A',
                    'user' => $item->user ?? (object) ['name' => 'N/A']
                ];
            });

            return view('kelas.index', compact('kelas'));
        } catch (\Exception $e) {
            Log::error('Error fetching kelas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data kelas: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        if (is_null($kodeProdi)) {
            Log::warning('Kode prodi is null for user: ' . Auth::user()->email);
            return redirect()->back()->with('error', 'Kode prodi tidak ditemukan.');
        }

        $matkul = Mk::where('kode_prodi', $kodeProdi)->get();
        $users = \App\Models\User::where('kode_prodi', $kodeProdi)->where('role', 'dosen')->get(); // Ambil hanya user dengan role dosen
        return view('kelas.create', compact('matkul', 'users'));
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
                'tahun_kurikulum' => 'required|date_format:Y',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'periode' => 'required',
                'nip_dosen' => 'required|exists:users,nip', // Mengganti dosen dengan users
            ]);

            Kelas::create([
                'kode_prodi' => $kodeProdi,
                'tahun_kurikulum' => $request->tahun_kurikulum,
                'kode_mk' => $request->kode_mk,
                'periode' => $request->periode,
                'nip_dosen' => $request->nip_dosen,
            ]);

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
            $users = \App\Models\User::where('kode_prodi', $kodeProdi)->where('role', 'dosen')->get(); // Ambil hanya user dengan role dosen
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
                'tahun_kurikulum' => 'required|date_format:Y',
                'kode_mk' => 'required|exists:mk,kode_mk',
                'periode' => 'required',
                'nip_dosen' => 'required|exists:users,nip', // Mengganti dosen dengan users
            ]);

            $kelas = Kelas::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $kelas->update([
                'tahun_kurikulum' => $request->tahun_kurikulum,
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

            $kelas = Kelas::where('kode_prodi', $kodeProdi)->findOrFail($id);
            $kelas->delete();

            return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Kelas not found with id ' . $id . ': ' . $e->getMessage());
            return redirect()->route('kelas.index')->with('error', 'Data kelas tidak ditemukan.');
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

            Excel::import(new KelasImport($kodeProdi), $file);

            return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Error importing kelas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'tahun_kurikulum');
        $sheet->setCellValue('B1', 'kode_mk');
        $sheet->setCellValue('C1', 'periode');
        $sheet->setCellValue('D1', 'nip_dosen');

        // Set contoh data sebagai teks
        $sheet->setCellValueExplicit('A2', '2025', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', 'MK001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '20251', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D2', '201114101', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set format kolom sebagai teks
        $sheet->getStyle('D2:D1000')->getNumberFormat()->setFormatCode('@'); // @ menandakan teks

        // Tambahkan petunjuk
        $sheet->setCellValue('A3', 'Catatan: Masukkan NIP tanpa tanda `, kolom ini otomatis diformat sebagai teks.');

        // Set header style
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'D') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'kelas_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_kelas.xlsx')->deleteFileAfterSend(true);
    }
}