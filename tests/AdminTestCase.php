<?php

namespace Tests;

/**
 * Base test case for admin area tests.
 * Wraps both manager_test and tenant_test connections in database transactions
 * so that data is properly rolled back after each test.
 */
abstract class AdminTestCase extends TestCase
{
    protected $connectionsToTransact = ['manager_test', 'tenant_test'];
}
