<?php

namespace Tests\Unit;

use App\Services\EmployeeLookupService;
use ReflectionMethod;
use Tests\TestCase;

class RoomRateTest extends TestCase
{
    protected function rate(?string $jobLevel): int
    {
        $service = new EmployeeLookupService;
        $method = new ReflectionMethod($service, 'roomRate');
        $method->setAccessible(true);

        return $method->invoke($service, $jobLevel);
    }

    public function test_high_levels_get_967000(): void
    {
        foreach (['Director', 'Deputy Director', 'Senior Manager', 'Manager'] as $level) {
            $this->assertSame(967000, $this->rate($level), "Gagal untuk job_level: {$level}");
        }
    }

    public function test_other_levels_get_676000(): void
    {
        foreach (['Asisten Manager', 'Officer', 'Senior Officer', 'Supervisor', 'Junior Supervisor', 'Senior Supervisor', 'Non Officer', null] as $level) {
            $this->assertSame(676000, $this->rate($level), 'Gagal untuk job_level: '.var_export($level, true));
        }
    }
}
