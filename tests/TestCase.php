<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication {
        createApplication as bootApplication;
    }

    /** The only database the test suite may ever touch. */
    public const TEST_DATABASE = 'smarterp_test';

    /** Set once the live connection has been confirmed for this process. */
    private static bool $databaseConfirmed = false;

    /**
     * Boots the app, then refuses to continue unless the default connection
     * points at smarterp_test. This runs before setUpTraits(), so no
     * transaction, migration or test query reaches any other database.
     */
    public function createApplication(): Application
    {
        $app = $this->bootApplication();

        $connection = $app['config']->get('database.default');
        $configured = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($configured !== self::TEST_DATABASE) {
            $this->abortSuite("configured database is '{$configured}'");
        }

        if (! self::$databaseConfirmed) {
            $connected = (string) DB::connection($connection)->selectOne('SELECT DATABASE() AS db')->db;
            if ($connected !== self::TEST_DATABASE) {
                $this->abortSuite("connected database is '{$connected}'");
            }
            self::$databaseConfirmed = true;
        }

        return $app;
    }

    private function abortSuite(string $reason): never
    {
        fwrite(STDERR, PHP_EOL.'ABORTED: tests may only run against '.self::TEST_DATABASE." ({$reason}).".PHP_EOL
            .'Check .env.testing / phpunit.xml and run "php artisan config:clear".'.PHP_EOL);
        exit(1);
    }
}
