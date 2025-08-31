-- Database setup SQL
-- This file creates the database and runs initial setup
-- For schema management, use Phinx: vendor/bin/phinx migrate

CREATE DATABASE IF NOT EXISTS api_rest;
USE api_rest;

-- Note: The actual table schema is managed through Phinx migrations
-- To set up the database schema, run: vendor/bin/phinx migrate