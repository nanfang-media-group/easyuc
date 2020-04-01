<?php

namespace SouthCN\EasyUC\Tests;

use Illuminate\Support\Env;
use Orchestra\Testbench\TestCase as BaseTestCase;
use SouthCN\EasyUC\EasyUCServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [EasyUCServiceProvider::class];
    }
}
