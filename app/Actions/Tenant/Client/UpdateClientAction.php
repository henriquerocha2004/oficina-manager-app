<?php

namespace App\Actions\Tenant\Client;

use App\Dto\ClientDto;
use App\Exceptions\Client\ClientNotFoundException;
use App\Models\Tenant\Client;
use Symfony\Component\Uid\Ulid;

use function ulid_db;

class UpdateClientAction
{
    public function __invoke(ClientDto $clientDto, Ulid $clientId): void
    {
        $idStr = ulid_db($clientId);
        $client = Client::query()->find($idStr);

        if (is_null($client)) {
            throw new ClientNotFoundException();
        }

        $client->update([
            'name' => $clientDto->name,
            'document_number' => $clientDto->document_number,
            'email' => $clientDto->email,
            'phone' => $clientDto->phone,
            'street' => $clientDto->street,
            'city' => $clientDto->city,
            'state' => $clientDto->state,
            'zip_code' => $clientDto->zip_code,
            'observations' => $clientDto->observations,
        ]);
    }
}
