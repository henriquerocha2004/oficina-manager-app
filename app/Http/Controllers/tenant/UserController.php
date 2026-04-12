<?php

namespace App\Http\Controllers\tenant;

use App\Actions\Tenant\User\CreateUserAction;
use App\Actions\Tenant\User\DeleteUserAction;
use App\Actions\Tenant\User\FindOneUserAction;
use App\Actions\Tenant\User\SearchUserAction;
use App\Actions\Tenant\User\UpdateUserAction;
use App\Constants\Messages;
use App\Dto\UserDto;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Requests\FindRequest;
use App\Http\Requests\tenant\AccountRequest;
use App\Http\Requests\tenant\UserRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('Tenant/Users/Index');
    }

    public function account(): InertiaResponse
    {
        return Inertia::render('Tenant/Account/Index');
    }

    public function store(UserRequest $request, CreateUserAction $createUserAction): JsonResponse
    {
        try {
            $user = $createUserAction(
                userDto: $request->toDto(),
                avatarFile: $request->file('avatar'),
            );

            return $this->setResponse(
                message: Messages::USER_CREATED_SUCCESS,
                code: Response::HTTP_CREATED,
                data: ['user' => $user],
            );
        } catch (UserAlreadyExistsException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_CONFLICT,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_CREATING_USER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_CREATING_USER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function find(FindRequest $request, SearchUserAction $searchUserAction): JsonResponse
    {
        try {
            $loggedUserId = $request->user('tenant')?->id;

            $users = $searchUserAction(
                searchDto: $request->toDto(),
                excludedUserId: $loggedUserId,
            );

            return $this->setResponse(
                message: Messages::USERS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['users' => $users],
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_USERS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_USERS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function findOne(int $id, FindOneUserAction $findOneUserAction): JsonResponse
    {
        try {
            $user = $findOneUserAction($id);

            return $this->setResponse(
                message: Messages::USERS_FETCHED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['user' => $user],
            );
        } catch (UserNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_FETCHING_USERS, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_FETCHING_USERS,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function update(UserRequest $request, int $id, UpdateUserAction $updateUserAction): JsonResponse
    {
        try {
            $updateUserAction(
                userDto: $request->toDto(),
                userId: $id,
                avatarFile: $request->file('avatar'),
                removeAvatar: $request->boolean('remove_avatar'),
            );

            return $this->setResponse(
                message: Messages::USER_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (UserNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (UserAlreadyExistsException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_CONFLICT,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_USER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_USER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function delete(int $id, DeleteUserAction $deleteUserAction): JsonResponse
    {
        try {
            $deleteUserAction($id);

            return $this->setResponse(
                message: Messages::USER_DELETED_SUCCESS,
                code: Response::HTTP_OK,
            );
        } catch (UserNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_DELETING_USER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_DELETING_USER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }

    public function updateAccount(AccountRequest $request, UpdateUserAction $updateUserAction): JsonResponse
    {
        try {
            $loggedUser = $request->user('tenant');

            $updateUserAction(
                userDto: UserDto::fromArray([
                    'name' => $request->validated('name'),
                    'email' => $loggedUser->email,
                    'role' => is_string($loggedUser->role) ? $loggedUser->role : $loggedUser->role?->value,
                    'password' => $request->validated('password'),
                    'is_active' => $loggedUser->is_active,
                ]),
                userId: $loggedUser->id,
                avatarFile: $request->file('avatar'),
                removeAvatar: $request->boolean('remove_avatar'),
            );

            return $this->setResponse(
                message: Messages::USER_UPDATED_SUCCESS,
                code: Response::HTTP_OK,
                data: ['user' => $loggedUser->refresh()],
            );
        } catch (UserNotFoundException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_NOT_FOUND,
            );
        } catch (UserAlreadyExistsException $exception) {
            return $this->setResponse(
                message: $exception->getMessage(),
                code: Response::HTTP_CONFLICT,
            );
        } catch (Exception $exception) {
            Log::error(Messages::ERROR_UPDATING_USER, [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);

            return $this->setResponse(
                message: Messages::ERROR_UPDATING_USER,
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
}
