<?php

use Dotenv\Dotenv;
use Mcp\Server\Transport\StdioTransport;
use Mcp\Server;
use Src\Services\DatabaseService;
use Src\Toolboxes\MemoryToolbox;

include 'vendor/autoload.php';

Dotenv::createImmutable(__DIR__)->load();

$database = DatabaseService::createDatabase();

DatabaseService::initDatabase($database);

$server = Server::builder()
    ->setServerInfo('Notitioner MCP', '1.0.0')
    ->addTool(function () use ($database): string {
        $toolbox = new MemoryToolbox($database);

        return $toolbox->getAvailableTags();
    }, 'get_available_tags')
    ->addTool(function (array $tags) use ($database): string {
        $toolbox = new MemoryToolbox($database);

        return $toolbox->searchNotes($tags);
    }, 'search_notes')
    ->addTool(function (string $title) use ($database): string {
        $toolbox = new MemoryToolbox($database);

        return $toolbox->readNote($title);
    }, 'read_note')
    ->addTool(function (string $title, string $note, array $tags, bool $overrideIfAlreadyExists = true) use ($database): string {
        $toolbox = new MemoryToolbox($database);

        return $toolbox->writeNote($title, $note, $tags, $overrideIfAlreadyExists);
    }, 'write_note')
    ->build();

$server->run(new StdioTransport());

