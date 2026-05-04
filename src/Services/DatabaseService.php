<?php

namespace Src\Services;

use Sentience\Database\Databases\Firebird\FirebirdDatabase;
use Sentience\Database\Databases\MySQL\MariaDBDatabase;
use Sentience\Database\Databases\MySQL\MySQLDatabase;
use Sentience\Database\Databases\PgSQL\PgSQLDatabase;
use Sentience\Database\Databases\SQLite\SQLiteDatabase;
use Sentience\Database\Databases\SQLServer\SQLServerDatabase;
use Sentience\Database\Databases\DatabaseInterface;
use Sentience\Database\Queries\Enums\ReferentialActionEnum;
use Sentience\Database\Queries\Query;

class DatabaseService
{
    public const string NOTES_TABLE = 'notitioner_mcp_notes';
    public const string TAGS_TABLE = 'notitioner_mcp_tags';

    public static function createDatabase(): DatabaseInterface
    {
        return match ($_ENV['DATABASE']) {
            'firebird' => FirebirdDatabase::network(
                file: $_ENV['FIREBIRD_DB_FILE'],
                username: $_ENV['FIREBIRD_DB_USERNAME'],
                password: $_ENV['FIREBIRD_DB_PASSWORD'],
                host: $_ENV['FIREBIRD_DB_HOST'],
                port: $_ENV['FIREBIRD_DB_PORT'],
            ),
            'mariadb' => MariaDBDatabase::network(
                name: $_ENV['MARIADB_DB_NAME'],
                username: $_ENV['MARIADB_DB_USERNAME'],
                password: $_ENV['MARIADB_DB_PASSWORD'],
                host: $_ENV['MARIADB_DB_HOST'],
                port: $_ENV['MARIADB_DB_PORT'],
            ),
            'mysql' => MySQLDatabase::network(
                name: $_ENV['MYSQL_DB_NAME'],
                username: $_ENV['MYSQL_DB_USERNAME'],
                password: $_ENV['MYSQL_DB_PASSWORD'],
                host: $_ENV['MYSQL_DB_HOST'],
                port: $_ENV['MYSQL_DB_PORT'],
            ),
            'postgres' => PgSQLDatabase::network(
                name: $_ENV['POSTGRES_DB_NAME'],
                username: $_ENV['POSTGRES_DB_USERNAME'],
                password: $_ENV['POSTGRES_DB_PASSWORD'],
                host: $_ENV['POSTGRES_DB_HOST'],
                port: $_ENV['POSTGRES_DB_PORT'],
            ),
            'sqlite' => SQLiteDatabase::file(
                file: $_ENV['SQLITE_DB_FILE'],
                usePDOAdapter: true
            ),
            'sqlserver' => SQLServerDatabase::network(
                name: $_ENV['SQLSERVER_DB_NAME'],
                username: $_ENV['SQLSERVER_DB_USERNAME'],
                password: $_ENV['SQLSERVER_DB_PASSWORD'],
                host: $_ENV['SQLSERVER_DB_HOST'],
                port: $_ENV['SQLSERVER_DB_PORT'],
            ),
            default => SQLiteDatabase::file(
                file: './database/motitioner-mcp.db',
            ),
        };
    }

    public static function initDatabase(DatabaseInterface $database): void
    {
        $database->createTable(static::NOTES_TABLE)
            ->ifNotExists()
            ->autoIncrement('id')
            ->string('title')
            ->text('note')
            ->dateTime('created_at', default: Query::currentTimestamp())
            ->execute();

        $database->createTable(static::TAGS_TABLE)
            ->ifNotExists()
            ->autoIncrement('id')
            ->int('note_id')
            ->text('tag')
            ->foreignKeyConstraint('note_id', static::NOTES_TABLE, 'id', referentialActions: [ReferentialActionEnum::ON_DELETE_CASCADE])
            ->execute();
    }
}
