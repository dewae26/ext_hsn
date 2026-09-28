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
     *     company_name: ?string
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
        ];
    }
}
