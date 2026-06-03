<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $compiledViewPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'be2-hospital-test-views' . DIRECTORY_SEPARATOR . getmypid();
        File::ensureDirectoryExists($compiledViewPath);
        config(['view.compiled' => $compiledViewPath]); // fixed: tránh cache Blade bị Windows lock/tranh ghi khi chạy test
    }
}
