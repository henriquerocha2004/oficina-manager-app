<?php

namespace App\Actions\Tenant\Client;

use App\Dto\ClientDto;
use App\Exceptions\Client\ClientAlreadyExistsException;
use App\Models\Tenant\Client;
use Throwable;

class CreateClientAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(ClientDto $clientDto): Client
    {
        $client = Client::query()
            ->whereDocumentNumber($clientDto->document_number)
            ->first();

        throw_if(!is_null($client), ClientAlreadyExistsException::class);

        return Client::query()->create($clientDto->toArray());
    }
}
