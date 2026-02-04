<?php

use Symfony\Component\Uid\Ulid;

if (!function_exists('tenant')) {
    function tenant()
    {
        return app('tenant');
    }
}

if (! function_exists('ulid_db')) {
    function ulid_db(Ulid|string $u): string
    {
        return strtolower((string) $u);
    }
}
