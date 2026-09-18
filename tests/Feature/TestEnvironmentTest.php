<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TestEnvironmentTest extends TestCase
{
    /**
     * The suite must run against whichever database it was pointed at.
     *
     * `phpunit.xml` selects SQLite for the everyday run, and the continuous
     * integration job overrides that with an environment variable to exercise
     * MariaDB instead. If that override ever stopped taking effect, the MariaDB
     * job would quietly re-run the SQLite suite and still report success — so
     * the two are asserted to agree.
     */
    public function test_the_suite_runs_on_the_database_it_was_pointed_at(): void
    {
        $this->assertSame(
            env('DB_CONNECTION', 'sqlite'),
            DB::connection()->getDriverName(),
            'the configured connection and the one in use disagree'
        );
    }

    public function test_the_application_is_in_testing_mode(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertNotSame('', config('app.key'), 'no application key is set');
    }
}
