<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\Mahasiswa;
use App\Models\NilaiCpl;
use App\Models\NilaiCpmk;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Imports\CplImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class CplController extends Controller
{
    public function index()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $mahasiswas = Mahasiswa::where('kode_prodi', $kodeProdi)->get(); // Filter mahasiswa berdasarkan prodi
        $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();
        // return view('penilaian_cpl.index', compact('mahasiswas', 'cpls'));
        return view('cpl.index', compact('mahasiswas', 'cpls'));
    }

    public function list()
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $cpls = Cpl::where('kode_prodi', $kodeProdi)->with('cpmks')->get();
        return view('cpl.index', compact('cpls')); // Daftar CPL sesuai prodi
    }

    public function create()
    {
        return view('cpl.create');
    }

    public function store(Request $request)
{
    try {
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            
            return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
        }

        $request->validate([
            'kode_cpl' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cpl')->where(function ($query) use ($kodeProdi) {
                    return $query->where('kode_prodi', $kodeProdi);
                }),
            ],
            'deskripsi' => 'required|string',
            'kategori' => 'required|string',
        ]);

        $data = [
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
            'kode_prodi' => $kodeProdi,
        ];

        $cpl = Cpl::create($data);

        if (!$cpl) {
            throw new \Exception('Gagal menyimpan data CPL.');
        }

        return redirect()->route('cpl.list')->with('success', 'Data CPL berhasil ditambahkan.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
    }
}

    public function edit($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $cpl = Cpl::where('kode_prodi', $kodeProdi)->findOrFail($id); // Pastikan hanya CPL dari prodi user yang bisa diedit
        return view('cpl.edit', compact('cpl'));
    }

    public function update(Request $request, $id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $validatedData = $request->validate([
            'kode_cpl' => 'required|string',
            'deskripsi' => 'required|string',
            'kategori' => 'required|string',
        ]);

        $cpl = Cpl::where('kode_prodi', $kodeProdi)->findOrFail($id); // Pastikan hanya CPL dari prodi user yang diupdate
        $cpl->update([
            'kode_cpl' => $request->kode_cpl,
            'deskripsi' => $request->deskripsi,
            'kategori' => $request->kategori,
            'kode_prodi' => $kodeProdi, // Pastikan kode_prodi tidak berubah
        ]);

        return redirect()->route('cpl.list')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $cpl = Cpl::where('kode_prodi', $kodeProdi)->findOrFail($id); // Pastikan hanya CPL dari prodi user yang dihapus
        $cpl->delete();

        return redirect()->route('cpl.list')->with('success', 'Data berhasil dihapus');
    }

    public function show($mahasiswa_id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $mahasiswa = Mahasiswa::where('kode_prodi', $kodeProdi)->findOrFail($mahasiswa_id); // Pastikan mahasiswa dari prodi yang sama
        $cpls = Cpl::where('kode_prodi', $kodeProdi)->with('cpmks')->get();
        $nilaiCpls = NilaiCpl::where('mahasiswa_id', $mahasiswa_id)->get();

        $cplScores = [];
        foreach ($cpls as $cpl) {
            $totalScore = 0;
            $totalBobot = 0;

            foreach ($cpl->cpmks as $cpmk) {
                $bobotCplCpmk = $cpmk->pivot->bobot ?? 0;
                $nilaiCpmk = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                    ->where('cpmk_id', $cpmk->id)
                    ->first();

                if ($nilaiCpmk) {
                    $nilai = $nilaiCpmk->nilai ?? 0;
                    $bobotMk = $cpmk->mks()->first()->pivot->bobot ?? 0;
                    $score = ($bobotMk * $nilai) / 100;
                    $totalScore += ($bobotCplCpmk * $score) / 100;
                    $totalBobot += $bobotCplCpmk;
                }
            }

            $cplScores[$cpl->id] = $totalBobot > 0 ? ($totalScore / ($totalBobot / 100)) : 0;
            NilaiCpl::updateOrCreate(
                ['mahasiswa_id' => $mahasiswa_id, 'cpl_id' => $cpl->id],
                ['nilai' => round($cplScores[$cpl->id], 2)]
            );
        }

        return view('penilaian_cpl.show', compact('mahasiswa', 'cpls', 'cplScores', 'nilaiCpls'));
    }

    public function calculateCplScore($mahasiswa_id, $cpl_id)
    {
        $kodeProdi = Auth::user()->kode_prodi;
        $cpl = Cpl::where('kode_prodi', $kodeProdi)->findOrFail($cpl_id); // Pastikan CPL dari prodi yang sama
        $totalScore = 0;
        $totalBobot = 0;

        foreach ($cpl->cpmks as $cpmk) {
            $bobotCplCpmk = $cpmk->pivot->bobot ?? 0;
            $nilaiCpmk = NilaiCpmk::where('mahasiswa_id', $mahasiswa_id)
                ->where('cpmk_id', $cpmk->id)
                ->first();

            if ($nilaiCpmk) {
                $nilai = $nilaiCpmk->nilai ?? 0;
                $bobotMk = $cpmk->mks()->first()->pivot->bobot ?? 0;
                $score = ($bobotMk * $nilai) / 100;
                $totalScore += ($bobotCplCpmk * $score) / 100;
                $totalBobot += $bobotCplCpmk;
            }
        }

        return $totalBobot > 0 ? round(($totalScore / ($totalBobot / 100)), 2) : 0;
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
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('cpl.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            // Impor file menggunakan CplImport
            Excel::import(new CplImport($kodeProdi), $file);

            return redirect()->route('cpl.list')->with('success', 'Data CPL berhasil diimpor.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Kode CPL');
        $sheet->setCellValue('B1', 'Deskripsi');
        $sheet->setCellValue('C1', 'Kategori');

        // Set header style
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        foreach (range('A', 'C') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'cpl_template');
        $writer->save($tempFile);

        return response()->download($tempFile, 'template_cpl.xlsx')->deleteFileAfterSend(true);
    }
}