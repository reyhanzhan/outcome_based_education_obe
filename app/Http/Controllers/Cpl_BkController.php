<?php

namespace App\Http\Controllers;

use App\Imports\CplBkImport;
use App\Models\Cpl;
use App\Models\Bk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kurikulum;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Cpl_BkController extends Controller
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
            if (!$kodeProdi) {
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $tahunFilter = request('tahun', session('selected_year', ''));
            if ($tahunFilter) {
                session(['selected_year' => $tahunFilter]);
            } else {
                $tahunFilter = session('selected_year', '');
            }
            Log::info("Index - kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");

            $kurikulumId = $tahunFilter ? Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahunFilter)->value('id') : null;
            Log::info("Index - kurikulumId: {$kurikulumId}");

            $cpls = Cpl::where('kode_prodi', $kodeProdi)->get();
            $bks = Bk::where('kode_prodi', $kodeProdi)->get();

            $query = DB::table('cpl_bk')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('bk_id', $bks->pluck('id'));
            if ($kurikulumId) {
                $query->where('kurikulum_id', $kurikulumId);
            } else {
                $query->whereNull('kurikulum_id'); // Fallback untuk data tanpa kurikulum_id
            }
            $pemetaan = $query->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpl_id . '-' . $item->bk_id => true];
                });

            Log::info("Successfully loaded pemetaan CPL-BK for kode_prodi: {$kodeProdi}, tahun: {$tahunFilter}, kurikulum_id: {$kurikulumId}, pemetaan count: " . count($pemetaan));

            return view('pemetaan_CPL-BK.index', compact('cpls', 'bks', 'pemetaan', 'tahunFilter'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPL-BK: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pemetaan: ' . $e->getMessage());
        }
    }

    public function update(Request $request)
{
    try {
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            return response()->json(['error' => 'Kode prodi tidak ditemukan.'], 403);
        }

        Log::info('Received data: ' . json_encode($request->all()));

        $request->validate([
            'cpl_id' => 'required|exists:cpl,id',
            'bk_id' => 'required|exists:bk,id',
            'checked' => 'required|boolean',
        ]);

        $cpl = Cpl::where('id', $request->cpl_id)->where('kode_prodi', $kodeProdi)->first();
        $bk = Bk::where('id', $request->bk_id)->where('kode_prodi', $kodeProdi)->first();

        if (!$cpl || !$bk) {
            return response()->json(['error' => 'CPL atau BK tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
        }

        $tahunFilter = session('selected_year', '');
        $kurikulumId = $tahunFilter ? Kurikulum::where('kode_prodi', $kodeProdi)->where('tahun', $tahunFilter)->value('id') : null;

        $checked = (bool) $request->checked;

        if ($checked) {
            DB::table('cpl_bk')->updateOrInsert(
                [
                    'cpl_id' => $request->cpl_id,
                    'bk_id' => $request->bk_id,
                    'kurikulum_id' => $kurikulumId, // Tambahkan kurikulum_id ke kunci utama
                ],
                [
                    'kurikulum_id' => $kurikulumId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            Log::info("Pemetaan CPL-BK tersimpan: CPL ID {$request->cpl_id} - BK ID {$request->bk_id}, kurikulum_id: {$kurikulumId}");
            return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
        } else {
            DB::table('cpl_bk')
                ->where('cpl_id', $request->cpl_id)
                ->where('bk_id', $request->bk_id)
                ->when($kurikulumId, function ($query) use ($kurikulumId) {
                    $query->where('kurikulum_id', $kurikulumId);
                })
                ->delete();
            Log::info("Pemetaan CPL-BK dihapus: CPL ID {$request->cpl_id} - BK ID {$request->bk_id}, kurikulum_id: {$kurikulumId}");
            return response()->json(['success' => '❌ Data pemetaan dihapus!']);
        }
    } catch (\Exception $e) {
        Log::error('Error updating pemetaan CPL-BK: ' . $e->getMessage());
        return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
    }
}

    public function downloadTemplate()
{
    try {
        $kodeProdi = Auth::user()->kode_prodi;
        if (!$kodeProdi) {
            throw new \Exception('Kode prodi tidak ditemukan untuk user ini.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ambil data CPL untuk header kolom
        $cpls = \App\Models\Cpl::where('kode_prodi', $kodeProdi)->get();
        if ($cpls->isEmpty()) {
            throw new \Exception('Tidak ada data CPL untuk prodi ini.');
        }
        $cplCount = $cpls->count();

        // Ambil data BK untuk baris
        $bks = \App\Models\Bk::where('kode_prodi', $kodeProdi)->distinct()->get();
        if ($bks->isEmpty()) {
            throw new \Exception('Tidak ada data BK untuk prodi ini.');
        }
        $bkCount = $bks->count();

        // Set header utama
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Kode BK');
        $sheet->setCellValue('C1', 'Deskripsi BK'); // Tambahkan deskripsi BK untuk informasi tambahan

        // Set header CPL di baris kedua (mulai dari kolom D)
        $startColIndex = 4; // Mulai dari kolom D (index 4)
        foreach ($cpls as $cpl) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startColIndex);
            $sheet->setCellValue($colLetter . '1', $cpl->kode_cpl);
            $startColIndex++;
        }
        $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + $cplCount); // A=1, B=2, C=3, lalu tambah CPL

        // Set header style
        $sheet->getStyle('A1:' . $endCol . '1')->getFont()->setBold(true);
        $sheet->getStyle('A1:' . $endCol . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Debug: Tampilkan data yang diambil
        \Illuminate\Support\Facades\Log::info("CPL data retrieved for kode_prodi {$kodeProdi}: " . $cpls->pluck('kode_cpl')->toJson());
        \Illuminate\Support\Facades\Log::info("BK data retrieved for kode_prodi {$kodeProdi}: " . $bks->pluck('kode_bk')->toJson());

        // Ambil pemetaan dari database
        $pemetaan = \Illuminate\Support\Facades\DB::table('cpl_bk')
            ->whereIn('cpl_id', $cpls->pluck('id'))
            ->whereIn('bk_id', $bks->pluck('id'))
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->bk_id . '-' . $item->cpl_id => true];
            });

        // Isi data berdasarkan BK sebagai baris dan CPL sebagai kolom
        $row = 2;
        $uniqueBks = $bks->unique('kode_bk');
        foreach ($uniqueBks as $index => $bk) {
            $sheet->setCellValue('A' . $row, $index + 1); // No
            $sheet->setCellValue('B' . $row, $bk->kode_bk ?? 'BK' . $bk->id); // Kode BK
            $sheet->setCellValue('C' . $row, $bk->deskripsi ?? ''); // Deskripsi BK
            $colIndex = 4; // Mulai dari kolom D
            foreach ($cpls as $cpl) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($colLetter . $row, isset($pemetaan[$bk->id . '-' . $cpl->id]) ? 'V' : '');
                $colIndex++;
            }
            $row++;
        }

        // Set header style untuk baris kedua
        $sheet->getStyle('A2:' . $endCol . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Auto-size columns
        $maxColIndex = 3 + $cplCount; // A=1, B=2, C=3, lalu tambah CPL
        for ($i = 1; $i <= $maxColIndex; $i++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
        }

        // Simpan file sementara
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'cpl_bk_template');
        $writer->save($tempFile);

        \Illuminate\Support\Facades\Log::info("Template CPL-BK downloaded for kode_prodi: {$kodeProdi} with {$cplCount} CPL columns and {$bkCount} BK rows");
        return response()->download($tempFile, 'template_cpl_bk.xlsx')->deleteFileAfterSend(true);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error generating template: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat mengunduh template: ' . $e->getMessage());
    }
}

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xls,xlsx,csv|max:2048',
            ]);

            if (!$request->hasFile('file') || !$request->file('file')->isValid()) {
                Log::error('Invalid or missing file uploaded.');
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('cpl_bk.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            if (!$kodeProdi) {
                Log::error('Kode prodi tidak ditemukan untuk user: ' . Auth::user()->id);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            $tahunFilter = request('tahun', session('selected_year', ''));
            if (!$tahunFilter) {
                Log::error("Tahun tidak ditemukan di session atau request untuk kode_prodi {$kodeProdi}.");
                return redirect()->back()->with('error', 'Tahun kurikulum tidak ditemukan. Pilih tahun terlebih dahulu.');
            }
            Log::info("Import - kodeProdi: {$kodeProdi}, tahunFilter: {$tahunFilter}");

            $kurikulum = Kurikulum::firstOrCreate(
                ['tahun' => $tahunFilter, 'kode_prodi' => $kodeProdi],
                ['kode_mk' => 'MK001', 'semester' => 1]
            );
            $kurikulumId = $kurikulum->id;
            Log::info("Import - kurikulumId from firstOrCreate: {$kurikulumId}");

            Log::info('Starting CPL-BK import for kode_prodi: ' . $kodeProdi . ', tahun: ' . $tahunFilter . ', kurikulum_id: ' . $kurikulumId);

            Excel::import(new CplBkImport($kodeProdi, $kurikulumId), $file);

            Log::info('CPL-BK import completed successfully for kode_prodi: ' . $kodeProdi . ', tahun: ' . $tahunFilter);
            return redirect()->route('Cpl_Bk.index', ['tahun' => $tahunFilter])->with('success', 'Data pemetaan CPL-BK berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}