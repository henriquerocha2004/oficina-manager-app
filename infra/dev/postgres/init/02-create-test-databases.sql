-- Create test databases for running unit and feature tests
-- These databases will have the same structure as production databases
-- but will be used exclusively for testing with transaction rollbacks

SELECT 'CREATE DATABASE oficina_manager_test;'
WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'oficina_manager_test'
)\gexec

SELECT 'CREATE DATABASE oficina_tenant_test;'
WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'oficina_tenant_test'
)\gexec
