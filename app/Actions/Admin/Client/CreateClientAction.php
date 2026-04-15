<?php

namespace App\Actions\Admin\Client;

use App\Dto\Admin\ClientDto;
use App\Exceptions\Admin\ClientAlreadyExistsException;
use App\Models\Admin\Client;
use Throwable;

class CreateClientAction
{
    /**
     * @throws Throwable
     */
    public function __invoke(ClientDto $clientDto): Client
    {
        $existing = Client::query()
            ->where('document', $clientDto->document)
            ->first();

        throw_if(!is_null($existing), ClientAlreadyExistsException::class);

        return Client::query()->create($clientDto->toArray());
    }
}
