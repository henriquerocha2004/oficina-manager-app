<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\Setting\GetSettingsAction;
use App\Actions\Tenant\Setting\UploadLogoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\tenant\UploadLogoRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class SettingController extends Controller
{
    public function index(
        Request $request,
        GetSettingsAction $getSettingsAction
    ): InertiaResponse {
        $tenant = app('tenant');

        return Inertia::render('Tenant/Settings/Index', [
            'tenant'   => $tenant->only(
                'name',
                'trade_name',
                'domain',
                'email',
                'document',
                'phone',
                'status',
                'is_active'
            ),
            'settings' => $getSettingsAction(),
        ]);
    }

    public function uploadLogo(
        UploadLogoRequest $request,
        UploadLogoAction $uploadLogoAction,
        GetSettingsAction $getSettingsAction
    ): JsonResponse {
        try {
            $uploadLogoAction($request->file('logo'));

            return $this->setResponse(
                message: 'Logomarca atualizada com sucesso.',
                code: Response::HTTP_OK,
                data: $getSettingsAction(),
            );
        } catch (Exception $e) {
            Log::error('Erro ao fazer upload da logomarca', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return $this->setResponse(
                message: 'Erro ao fazer upload da logomarca.',
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function removeLogo(
        UploadLogoAction $uploadLogoAction,
        GetSettingsAction $getSettingsAction
    ): JsonResponse {
        try {
            $uploadLogoAction(null);

            return $this->setResponse(
                message: 'Logomarca removida com sucesso.',
                code: Response::HTTP_OK,
                data: $getSettingsAction(),
            );
        } catch (Exception $e) {
            Log::error('Erro ao remover logomarca', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);

            return $this->setResponse(
                message: 'Erro ao remover logomarca.',
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
