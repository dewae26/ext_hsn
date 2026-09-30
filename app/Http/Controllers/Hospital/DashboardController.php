<?php

namespace App\Http\Controllers\Hospital;

use App\Http\Controllers\Controller;
use App\Models\HospitalContact;
use App\Models\VerificationLog;
use App\Services\EmployeeLookupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function __construct(protected EmployeeLookupService $lookup) {}

    public function index(Request $request)
    {
        $hospital = $request->attributes->get('hospital');
        $result = $request->session()->get('lookup_result');

        return view('hospital.dashboard', compact('hospital', 'result'));
    }

    public function lookup(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        $validated = $request->validate([
            'nrp' => ['required', 'string', 'max:25'],
        ]);

        $result = $this->lookup->verify($validated['nrp']);

        // Pastikan contact_id dari sesi masih valid (bisa berubah bila admin
        // mengedit daftar nomor RS). Jika tidak ada, simpan sebagai null.
        $contactId = $request->session()->get('hospital_contact_id');
        if ($contactId && ! HospitalContact::whereKey($contactId)->where('hospital_id', $hospital->id)->exists()) {
            $contactId = null;
        }

        VerificationLog::create([
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'hospital_contact_id' => $contactId,
            'pic_phone' => $request->session()->get('hospital_pic_phone'),
            'pic_label' => $request->session()->get('hospital_pic_label'),
            'nrp' => $result['nrp'],
            'employee_name' => $result['employee_name'],
            'group_company' => $result['group_company'],
            'company_name' => $result['company_name'],
            'ktp' => $result['ktp'] ?? null,
            'room_rate' => $result['room_rate'] ?? null,
            'result' => $result['result'],
            'feedback' => $result['feedback'],
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return redirect()->route('hospital.dashboard', ['hospital' => $hospital->slug])
            ->with('lookup_result', $result);
    }

    /**
     * Tampilkan PKS (view-only, inline) untuk pihak RS.
     */
    public function pks(Request $request)
    {
        $hospital = $request->attributes->get('hospital');
        $disk = Storage::disk(config('hasnurverif.pks.disk'));

        if (! $hospital->pks_path || ! $disk->exists($hospital->pks_path)) {
            abort(404);
        }

        return response()->file($disk->path($hospital->pks_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="PKS-'.$hospital->slug.'.pdf"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
