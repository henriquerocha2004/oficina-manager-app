<?php

namespace App\Http\Controllers\Api;

use App\Constants\Messages;
use App\Http\Controllers\Controller;
use App\Http\Requests\TrialRequest;
use App\Services\Admin\ClientTenantService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TrialController extends Controller
{
    public function store(TrialRequest $request, ClientTenantService $clientTenantService): JsonResponse
    {
        try {
            $result = $clientTenantService->create(
                clientDto: $request->toClientDto(),
                domain: $request->tenantDomain(),
                status: $request->tenantStatus(),
                trialUntil: $request->tenantTrialUntil(),
            );

            return $this->setResponse(
                message: Messages::TRIAL_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: $result,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_TRIAL, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_TRIAL,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
