<?php

namespace App\Constants;

class Messages
{
    public const string CLIENT_CREATED_SUCCESS = 'Client created successfully.';
    public const string ERROR_CREATING_CLIENT = 'An error occurred while creating the client.';
    public const string TENANT_NOT_FOUND = 'Tenant not found.';
    public const string WE_NOT_RECOGNIZE_TENANT = 'We do not recognize this tenant. Contact support!';
    public const string UNABLE_TO_SWITCH_TENANT = 'Unable to switch tenant.';
    public const string FAILED_TO_CREATE_DATABASE_TENANT = 'Failed to create database for tenant.';
    public const string FAILED_TO_CREATE_DATABASE = 'Falha ao criar banco de dados para o novo cliente.';
    public const string ERROR_FETCHING_CLIENTS = 'failed to fetch clients.';
    public const string CLIENTS_FETCHED_SUCCESS = 'Clients fetched successfully.';
    public const string CLIENT_UPDATED_SUCCESS = 'Client updated successfully.';
    public const string ERROR_UPDATING_CLIENT = 'An error occurred while updating the client.';
    public const string CLIENT_DELETED_SUCCESS = 'Client deleted successfully.';
    public const string ERROR_DELETING_CLIENT = 'An error occurred while deleting the client.';
}
