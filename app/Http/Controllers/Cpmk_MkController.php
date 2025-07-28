<?php

namespace App\Http\Controllers;

use App\Models\Mk;
use App\Models\Cpmk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Imports\CpmkMkImport;
use Maatwebsite\Excel\Facades\Excel;

class Cpmk_MkController extends Controller
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

            $cpmks = Cpmk::where('kode_prodi', $kodeProdi)->get();
            $mks = Mk::where('kode_prodi', $kodeProdi)->get();

            $pemetaan = DB::table('cpmk_mk')
                ->whereIn('cpmk_id', $cpmks->pluck('id'))
                ->whereIn('mk_id', $mks->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->cpmk_id . '-' . $item->mk_id => [
                            'bobot' => $item->bobot,
                            'min_standard' => $item->min_standard
                        ]
                    ];
                });

            Log::info("Successfully loaded pemetaan CPMK-MK for kode_prodi: {$kodeProdi}, CPMK count: {$cpmks->count()}, MK count: {$mks->count()}");

            return view('pemetaan_CPMK-MK.index', compact('cpmks', 'mks', 'pemetaan'));
        } catch (\Exception $e) {
            Log::error('Error loading pemetaan CPMK-MK: ' . $e->getMessage());
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
                'mk_id' => 'required|exists:mk,id',
                'cpmk_id' => 'required|exists:cpmk,id',
                'checked' => 'required|boolean',
                'bobot' => 'nullable|integer|min:0|max:100',
                'min_standard' => 'nullable|numeric|min:0|max:100'
            ]);

            $mk = Mk::where('id', $request->mk_id)->where('kode_prodi', $kodeProdi)->first();
            $cpmk = Cpmk::where('id', $request->cpmk_id)->where('kode_prodi', $kodeProdi)->first();

            if (!$mk || !$cpmk) {
                return response()->json(['error' => 'MK atau CPMK tidak ditemukan atau tidak sesuai dengan prodi Anda.'], 403);
            }

            $bobot = $request->bobot ?? 0;
            $min_standard = $request->min_standard ?? 50;
            $checked = (bool) $request->checked;

            if ($checked) {
                DB::table('cpmk_mk')->updateOrInsert(
                    [
                        'mk_id' => $request->mk_id,
                        'cpmk_id' => $request->cpmk_id,
                    ],
                    [
                        'bobot' => $bobot,
                        'min_standard' => $min_standard,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                // Pastikan data CPMK konsisten
                if (!$cpmk->deskripsi) {
                    $cpmk->deskripsi = 'Deskripsi tidak tersedia';
                    $cpmk->save();
                }

                Log::info("Pemetaan CPMK-MK tersimpan: CPMK ID {$request->cpmk_id} - MK ID {$request->mk_id}, Bobot: {$bobot}, Min Standard: {$min_standard}");
                return response()->json(['success' => '✅ Data pemetaan tersimpan!']);
            } else {
                DB::table('cpmk_mk')
                    ->where('mk_id', $request->mk_id)
                    ->where('cpmk_id', $request->cpmk_id)
                    ->delete();

                Log::info("Pemetaan CPMK-MK dihapus: CPMK ID {$request->cpmk_id} - MK ID {$request->mk_id}");
                return response()->json(['success' => '❌ Data pemetaan dihapus!']);
            }
        } catch (\Exception $e) {
            Log::error('Error updating pemetaan CPMK-MK: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menyimpan pemetaan: ' . $e->getMessage()], 500);
        }
    }

    public function getCpmks($mk_id)
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                return response()->json(['error' => 'Kode prodi tidak ditemukan.'], 403);
            }

            $mk = Mk::where('id', $mk_id)
                ->where('kode_prodi', $kodeProdi)
                ->with([
                    'cpmks' => function ($query) {
                        $query->select('cpmk.id', 'cpmk.kode_cpmk', 'cpmk.deskripsi')
                            ->withPivot('bobot', 'min_standard');
                    }
                ])
                ->firstOrFail();

            $cpmks = $mk->cpmks->map(function ($cpmk) {
                return [
                    'id' => $cpmk->id,
                    'kode_cpmk' => $cpmk->kode_cpmk,
                    'deskripsi' => $cpmk->deskripsi ?? 'Deskripsi tidak tersedia',
                    'bobot' => $cpmk->pivot->bobot ?? 0,
                    'min_standard' => $cpmk->pivot->min_standard ?? 50
                ];
            });

            Log::info("Successfully retrieved CPMKs for MK ID {$mk_id}, count: {$cpmks->count()}");
            return response()->json($cpmks);
        } catch (\Exception $e) {
            Log::error('Error retrieving CPMKs for MK ID {$mk_id}: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data CPMK: ' . $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        try {
            $kodeProdi = Auth::user()->kode_prodi;
            if (!$kodeProdi) {
                throw new \Exception('Kode prodi tidak ditemukan untuk user ini.');
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header utama
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Kode CPMK');
            $mks = Mk::where('kode_prodi', $kodeProdi)->distinct()->get();
            if ($mks->isEmpty()) {
                throw new \Exception('Tidak ada data MK untuk prodi ini.');
            }
            $mkCount = $mks->count();
            $endCol = Coordinate::stringFromColumnIndex(2 + $mkCount); // 2 = A and B, then add MK columns
            $sheet->mergeCells('C1:' . $endCol . '1');
            $sheet->setCellValue('C1', 'MK');

            // Debug: Tampilkan data MK yang diambil
            Log::info("MK data retrieved for kode_prodi {$kodeProdi}: " . $mks->pluck('kode_mk')->toJson());

            // Set header MK di baris kedua
            $colIndex = 3; // Start at column C (index 3)
            $uniqueMks = $mks->unique('kode_mk'); // Pastikan tidak ada duplikat berdasarkan kode_mk
            foreach ($uniqueMks as $mk) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->setCellValue($colLetter . '2', $mk->kode_mk ?? 'MK' . $mk->id);
                Log::debug("Setting header at {$colLetter}2 with value: " . ($mk->kode_mk ?? 'MK' . $mk->id)); // Debug header
                $colIndex++;
            }

            // Set header style
            $sheet->getStyle('A1:' . $endCol . '1')->getFont()->setBold(true);
            $sheet->getStyle('A1:' . $endCol . '1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2:' . $endCol . '2')->getFont()->setBold(true);
            $sheet->getStyle('A2:' . $endCol . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Ambil data CPMK dan MK dari database
            $cpmks = Cpmk::where('kode_prodi', $kodeProdi)->get();
            if ($cpmks->isEmpty()) {
                throw new \Exception('Tidak ada data CPMK untuk prodi ini.');
            }
            $pemetaan = DB::table('cpmk_mk')
                ->whereIn('cpmk_id', $cpmks->pluck('id'))
                ->whereIn('mk_id', $mks->pluck('id'))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->cpmk_id . '-' . $item->mk_id => true];
                });

            // Isi data berdasarkan kombinasi CPMK dan MK
            $row = 3;
            foreach ($cpmks as $index => $cpmk) {
                $sheet->setCellValue('A' . $row, $index + 1); // No
                $sheet->setCellValue('B' . $row, $cpmk->kode_cpmk); // Kode CPMK
                $colIndex = 3; // Start at column C
                
                foreach ($uniqueMks as $mk) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                    $value = isset($pemetaan[$cpmk->id . '-' . $mk->id]) ? 'V' : '';
                    $sheet->setCellValue($colLetter . $row, $value);
                    Log::debug("Setting value at {$colLetter}{$row}: {$value}"); // Debug data
                    $colIndex++;
                }
                $row++;
            }

            // Auto-size columns
            $maxColIndex = 2 + $uniqueMks->count(); // A=1, B=2, C+ = 3+
            for ($i = 1; $i <= $maxColIndex; $i++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
            }

            // Simpan file sementara
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'cpmk_mk_template');
            $writer->save($tempFile);

            Log::info("Template CPMK-MK downloaded for kode_prodi: {$kodeProdi} with {$mkCount} MK columns");
            return response()->download($tempFile, 'template_cpmk_mk.xlsx')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error generating template: ' . $e->getMessage());
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
                Log::error('Invalid or missing file uploaded.');
                return redirect()->back()->with('error', 'File yang diunggah tidak valid atau rusak. Harap unggah file Excel/CSV yang benar. <a href="' . route('cpmk_mk.template') . '">Download template</a>.');
            }

            $file = $request->file('file');
            $kodeProdi = Auth::user()->kode_prodi;

            if (!$kodeProdi) {
                Log::error('Kode prodi tidak ditemukan untuk user: ' . Auth::user()->id);
                return redirect()->back()->with('error', 'Kode prodi tidak ditemukan untuk user ini. Hubungi admin.');
            }

            // Log informasi impor
            Log::info('Starting CPMK-MK import for kode_prodi: ' . $kodeProdi);

            // Impor file menggunakan CpmkMkImport
            Excel::import(new CpmkMkImport($kodeProdi), $file);

            Log::info('CPMK-MK import completed successfully for kode_prodi: ' . $kodeProdi);
            return redirect()->route('Cpmk_Mk.index')->with('success', 'Data pemetaan CPMK-MK berhasil diimpor.');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }
}