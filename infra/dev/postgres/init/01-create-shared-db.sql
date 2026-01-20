SELECT 'CREATE DATABASE manager_database;'
WHERE NOT EXISTS (
    SELECT FROM pg_database WHERE datname = 'manager_database'
)\gexec
