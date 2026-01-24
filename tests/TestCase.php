<?php

namespace Tests;

use App\Models\Admin\Tenant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tests\Traits\MultiTenantDatabaseTransactions;

abstract class TestCase extends BaseTestCase
{
}
