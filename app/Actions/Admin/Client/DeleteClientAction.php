<?php

namespace App\Actions\Admin\Client;

use App\Exceptions\Admin\ClientNotFoundException;
use App\Models\Admin\Client;

class DeleteClientAction
{
    public function __invoke(string $id): void
    {
        $client = Client::query()->find($id);

        if ($client === null) {
            throw new ClientNotFoundException();
        }

        $client->delete();
    }
}
