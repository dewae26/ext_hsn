<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\HospitalLoginLog;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /** @var array<int, string> */
    protected array $palette = [
        '#001F95', '#00923F', '#DA251D', '#0D6EFD', '#6F42C1',
        '#20C997', '#fd7e14', '#D63384', '#0DCAF0', '#00136B',
        '#6C757D', '#66D1E5',
    ];

    public function index(Request $request)
    {
        $days = (int) $request->integer('days', 14);
        if (! in_array($days, [7, 14, 30], true)) {
            $days = 14;
        }

        $stats = [
            'hospitals_total' => Hospital::count(),
            'hospitals_active' => Hospital::where('is_active', true)->count(),
            'logins_today' => HospitalLoginLog::where('status', 'success')
                ->whereDate('created_at', Carbon::today())->count(),
            'lookups_today' => VerificationLog::whereDate('created_at', Carbon::today())->count(),
        ];

        $recentVerifications = VerificationLog::with('hospital')
            ->latest()
            ->limit(10)
            ->get();

        $recentLogins = HospitalLoginLog::with('hospital')
            ->latest()
            ->limit(10)
            ->get();

        [$chartLabels, $chartDatasets, $ranking, $chartTotal] = $this->verificationChart($days);

        return view('admin.dashboard', compact(
            'stats',
            'recentVerifications',
            'recentLogins',
            'chartLabels',
            'chartDatasets',
            'ranking',
            'chartTotal',
            'days',
        ));
    }

    /**
     * Bangun data grafik pengecekan NRP per RS per hari.
     *
     * @return array{0: array<int, string>, 1: array<int, array<string, mixed>>, 2: array<int, array<string, mixed>>, 3: int}
     */
    protected function verificationChart(int $days): array
    {
        $start = Carbon::today()->subDays($days - 1);

        $logs = VerificationLog::query()
            ->where('created_at', '>=', $start->copy()->startOfDay())
            ->get(['hospital_id', 'hospital_name', 'created_at']);

        // withTrashed: sertakan RS yang sudah dinonaktifkan/dihapus agar data
        // historisnya tetap muncul di grafik.
        $hospitals = Hospital::withTrashed()->get(['id', 'name'])->keyBy('id');

        $labelKeys = [];
        $labelDisplay = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $labelKeys[] = $date->format('Y-m-d');
            $labelDisplay[] = $date->format('d/m');
        }

        // Matriks jumlah per hospital_id per tanggal + nama RS untuk label.
        $matrix = [];
        $names = [];
        foreach ($logs as $log) {
            $key = $log->created_at->format('Y-m-d');
            $id = $log->hospital_id;
            $matrix[$id][$key] = ($matrix[$id][$key] ?? 0) + 1;
            $names[$id] = $hospitals->get($id)?->name ?? $log->hospital_name ?? 'RS (dihapus)';
        }

        // Urutkan berdasarkan nama RS agar urutan warna konsisten.
        uksort($matrix, fn ($a, $b) => strcasecmp($names[$a] ?? '', $names[$b] ?? ''));

        $datasets = [];
        $ranking = [];
        $chartTotal = 0;
        $colorIndex = 0;

        foreach ($matrix as $id => $dayCounts) {
            $data = array_map(fn ($day) => $dayCounts[$day] ?? 0, $labelKeys);
            $total = array_sum($data);

            if ($total === 0) {
                continue;
            }

            $label = $names[$id];
            $color = $this->palette[$colorIndex % count($this->palette)];
            $colorIndex++;
            $chartTotal += $total;

            $datasets[] = [
                'label' => $label,
                'data' => $data,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'borderWidth' => 1,
            ];

            $ranking[] = ['name' => $label, 'total' => $total, 'color' => $color];
        }

        usort($ranking, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [$labelDisplay, $datasets, array_slice($ranking, 0, 5), $chartTotal];
    }
}
