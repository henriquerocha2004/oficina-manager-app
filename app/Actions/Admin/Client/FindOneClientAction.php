<?php

namespace App\Actions\Admin\Client;

use App\Exceptions\Admin\ClientNotFoundException;
use App\Models\Admin\Client;

class FindOneClientAction
{
    public function __invoke(string $id): Client
    {
        $client = Client::query()->find($id);

        if ($client === null) {
            throw new ClientNotFoundException();
        }

        return $client;
    }
}
