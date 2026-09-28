<?php

namespace App\Http\Controllers\Admin;

use App\Exports\VerificationLogsExport;
use App\Http\Controllers\Controller;
use App\Models\Hospital;
use App\Models\HospitalLoginLog;
use App\Models\VerificationLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LogController extends Controller
{
    public function verifications(Request $request)
    {
        $filters = $this->filters($request);

        $logs = $this->verificationQuery($filters)
            ->with('hospital')
            ->latest()
            ->get();

        $hospitals = Hospital::orderBy('name')->get();

        return view('admin.logs.verifications', compact('logs', 'hospitals', 'filters'));
    }

    public function logins(Request $request)
    {
        $filters = $this->filters($request);

        $logs = HospitalLoginLog::query()
            ->with('hospital')
            ->when($filters['hospital_id'], fn ($q, $v) => $q->where('hospital_id', $v))
            ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->get();

        $hospitals = Hospital::orderBy('name')->get();

        return view('admin.logs.logins', compact('logs', 'hospitals', 'filters'));
    }

    public function exportVerifications(Request $request)
    {
        $filters = $this->filters($request);
        $filename = 'log-verifikasi-'.now()->format('Ymd-His').'.xlsx';

        $query = $this->verificationQuery($filters)
            ->with('hospital')
            ->latest();

        return Excel::download(new VerificationLogsExport($query), $filename);
    }

    /**
     * @return array<string, mixed>
     */
    protected function filters(Request $request): array
    {
        return [
            'hospital_id' => $request->integer('hospital_id') ?: null,
            'status' => $request->input('status'),
            'result' => $request->input('result'),
            'nrp' => $request->string('nrp')->trim()->toString() ?: null,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];
    }

    protected function verificationQuery(array $filters)
    {
        return VerificationLog::query()
            ->when($filters['hospital_id'], fn ($q, $v) => $q->where('hospital_id', $v))
            ->when($filters['result'], fn ($q, $v) => $q->where('result', $v))
            ->when($filters['nrp'], fn ($q, $v) => $q->where('nrp', 'like', "%{$v}%"))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
    }
}
