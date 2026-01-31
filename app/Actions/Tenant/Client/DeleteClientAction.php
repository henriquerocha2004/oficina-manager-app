<?php

namespace App\Actions\Tenant\Client;

use App\Exceptions\Client\ClientNotFoundException;
use App\Models\Tenant\Client;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class DeleteClientAction
{
    public function __invoke(Ulid $id): void
    {
        $idStr = ulid_db($id);
        $client = Client::query()->find($idStr);

        if ($client === null) {
            throw new ClientNotFoundException();
        }

        $client->delete();
    }
}
