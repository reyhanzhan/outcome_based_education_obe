<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\MahasiswaImport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class MahasiswaController extends Controller
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
            $mahasiswas = Mahasiswa::where('kode_prodi', $kodeProdi)->get();
            return view('mahasiswa.index', compact('mahasiswas'));
        } catch (\Exception $e) {
            Log::error('Error fetching mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data mahasiswa.');
        }
    }

    public function create()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        return view('mahasiswa.create', compact('kodeProdi'));
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
                'nim' => 'required|unique:mahasiswa,nim|alpha_num',
                'nama' => 'required|string|max:255',
                'periode_masuk' => 'required|string',
                'sistem_kuliah' => 'required|string|in:Reguler Pagi,Reguler Sore',
                'jalur_penerimaan' => 'required|string|in:SBMPTN,Seleksi Mandiri,Seleksi Mandiri PTS,Ujian Masuk Bersama PTS(UMB-PTS)',
                'gelombang_daftar' => 'required|string|in:K1-01,K1-02,K1-03',
                'agama' => 'required|string|in:Islam,Kristen,Hindu,Buddha,Khonghucu',
            ]);

            Mahasiswa::create([
                'nim' => $request->nim,
                'nama' => $request->nama,
                'periode_masuk' => $request->periode_masuk,
                'sistem_kuliah' => $request->sistem_kuliah,
                'jalur_penerimaan' => $request->jalur_penerimaan,
                'gelombang_daftar' => $request->gelombang_daftar,
                'agama' => $request->agama,
                'kode_prodi' => $kodeProdi,
            ]);

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error storing mahasiswa: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error storing mahasiswa: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data mahasiswa: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            if ($mahasiswa->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data mahasiswa ini.');
            }
            return view('mahasiswa.edit', compact('mahasiswa'));
        } catch (\Exception $e) {
            Log::error('Error fetching mahasiswa for edit: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data mahasiswa.');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            if ($mahasiswa->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data mahasiswa ini.');
            }

            Log::info('Request data received for update: ', $request->all());

            $request->validate([
                'nim' => 'required|alpha_num|unique:mahasiswa,nim,' . $id,
                'nama' => 'required|string|max:255',
                'periode_masuk' => 'required|string',
                'sistem_kuliah' => 'required|string|in:Reguler Pagi,Reguler Sore',
                'jalur_penerimaan' => 'required|string|in:SBMPTN,Seleksi Mandiri,Seleksi Mandiri PTS,Ujian Masuk Bersama PTS(UMB-PTS)',
                'gelombang_daftar' => 'required|string|in:K1-01,K1-02,K1-03',
                'agama' => 'required|string|in:Islam,Kristen,Hindu,Buddha,Khonghucu',
            ]);

            $data = [
                'nim' => $request->nim,
                'nama' => $request->nama,
                'periode_masuk' => $request->periode_masuk,
                'sistem_kuliah' => $request->sistem_kuliah,
                'jalur_penerimaan' => $request->jalur_penerimaan,
                'gelombang_daftar' => $request->gelombang_daftar,
                'agama' => $request->agama,
                'kode_prodi' => Auth::user()->kode_prodi,
            ];

            $mahasiswa->update($data);

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error updating mahasiswa: ' . $e->getMessage());
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating mahasiswa: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui data mahasiswa: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            if ($mahasiswa->kode_prodi !== Auth::user()->kode_prodi) {
                abort(403, 'Anda tidak memiliki akses ke data mahasiswa ini.');
            }
            $mahasiswa->delete();

            return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus!');
        } catch (\Exception $e) {
            Log::error('Error deleting mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data mahasiswa.');
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
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('mahasiswa.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            // Impor file menggunakan MahasiswaImport
            Excel::import(new MahasiswaImport($kodeProdi), $file);

            return redirect()->route('mahasiswa.index')->with('success', 'Data mahasiswa berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Error importing mahasiswa: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'nim');
        $sheet->setCellValue('B1', 'nama');
        $sheet->setCellValue('C1', 'periode_masuk');
        $sheet->setCellValue('D1', 'sistem_kuliah');
        $sheet->setCellValue('E1', 'jalur_penerimaan');
        $sheet->setCellValue('F1', 'gelombang_daftar');
        $sheet->setCellValue('G1', 'agama');

        // Set contoh data sebagai teks
        $sheet->setCellValueExplicit('A2', '2025010001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('B2', 'John Doe', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('C2', '2025', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('D2', 'Reguler Pagi', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('E2', 'SBMPTN', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('F2', 'K1-01', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValueExplicit('G2', 'Islam', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Set header style
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'mahasiswa_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_mahasiswa.xlsx')->deleteFileAfterSend(true);
    }
}