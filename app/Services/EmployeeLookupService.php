<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeLookupService
{
    /**
     * Cari karyawan berdasarkan NRP pada database MHCIS (read-only).
     *
     * @return array{
     *     result: string,
     *     feedback: string,
     *     nrp: string,
     *     employee_name: ?string,
     *     group_company: ?string,
     *     company_name: ?string,
     *     ktp: ?string,
     *     job_level: ?string,
     *     room_rate: ?int
     * }
     */
    public function verify(string $nrp): array
    {
        $nrp = trim($nrp);

        $employee = Employee::withTrashed()
            ->where('employee_id', $nrp)
            ->first();

        if (! $employee) {
            return [
                'result' => 'not_found',
                'feedback' => 'NRP tidak ditemukan',
                'nrp' => $nrp,
                'employee_name' => null,
                'group_company' => null,
                'company_name' => null,
                'ktp' => null,
                'job_level' => null,
                'room_rate' => null,
            ];
        }

        $isActive = $employee->deleted_at === null;

        return [
            'result' => $isActive ? 'active' : 'inactive',
            'feedback' => $isActive ? 'Karyawan Aktif' : 'Karyawan Tidak Aktif',
            'nrp' => $employee->employee_id,
            'employee_name' => $employee->fullname,
            'group_company' => $employee->group_company,
            'company_name' => $employee->company_name,
            'ktp' => $employee->ktp,
            'job_level' => $employee->job_level,
            'room_rate' => $this->roomRate($employee->job_level),
        ];
    }

    /**
     * Nominal kamar per malam berdasarkan job_level.
     */
    protected function roomRate(?string $jobLevel): int
    {
        $highLevels = config('hasnurverif.room_rate.high_levels', []);

        $isHigh = in_array(trim((string) $jobLevel), $highLevels, true);

        return (int) ($isHigh
            ? config('hasnurverif.room_rate.high')
            : config('hasnurverif.room_rate.regular'));
    }
}
