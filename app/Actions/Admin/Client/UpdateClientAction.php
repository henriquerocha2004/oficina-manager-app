<?php

namespace App\Actions\Admin\Client;

use App\Dto\Admin\ClientDto;
use App\Exceptions\Admin\ClientNotFoundException;
use App\Models\Admin\Client;

class UpdateClientAction
{
    public function __invoke(ClientDto $clientDto, string $id): void
    {
        $client = Client::query()->find($id);

        if (is_null($client)) {
            throw new ClientNotFoundException();
        }

        $client->update([
            'name' => $clientDto->name,
            'email' => $clientDto->email,
            'document' => $clientDto->document,
            'phone' => $clientDto->phone,
            'street' => $clientDto->street,
            'city' => $clientDto->city,
            'state' => $clientDto->state,
            'zip_code' => $clientDto->zip_code,
        ]);
    }
}
