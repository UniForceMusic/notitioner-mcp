<?php

namespace Src\Toolboxes;

use Sentience\Database\Queries\Objects\Join;
use Sentience\Database\Queries\Objects\WhereGroup;
use Src\Services\DatabaseService;

class MemoryToolbox extends Toolbox
{
    public function getAvailableTags(): string
    {
        return json_encode(
            $this->database->selectTable(DatabaseService::TAGS_TABLE)
                ->columns(['tag'])
                ->groupBy(['tag'])
                ->execute()
                ->fetchAssocs()
        );
    }

    public function searchNotes(array $tags): string
    {
        return json_encode(
            $this->database->selectTable(DatabaseService::NOTES_TABLE)
                ->columns(['title'])
                ->innerJoin(DatabaseService::TAGS_TABLE, fn(Join $join) => $join->on(
                    [DatabaseService::TAGS_TABLE, 'note_id'],
                    [DatabaseService::NOTES_TABLE, 'id']
                ))
                ->whereGroup(function (WhereGroup $group) use ($tags): void {
                    foreach ($tags as $tag) {
                        $group->orWhereLike('tag', $tag, true);
                    }
                })
                ->execute()
                ->fetchAssocs()
        );
    }

    public function readNote(string $title): string
    {
        return json_encode(
            $this->database->selectTable(DatabaseService::NOTES_TABLE)
                ->columns(['title', 'note'])
                ->whereEquals('title', $title)
                ->execute()
                ->fetchAssocs()
        );
    }

    public function writeNote(string $title, string $note, array $tags, bool $overrideIfAlreadyExists = true): string
    {
        $exists = $this->database->selectTable(DatabaseService::NOTES_TABLE)
            ->whereEquals('title', $title)
            ->limit(1)
            ->count() > 0;

        if ($exists && !$overrideIfAlreadyExists) {
            return 'Note saved';
        }

        $this->database->delete(DatabaseService::NOTES_TABLE)
            ->whereEquals('title', $title)
            ->execute();

        $id = $this->database->insert(DatabaseService::NOTES_TABLE)
            ->values([
                'title' => $title,
                'note' => $note
            ])
            ->returning(['id'])
            ->lastInsertId('id')
            ->execute()
            ->scalar('id');

        foreach ($tags as $tag) {
            $this->database->insert(DatabaseService::TAGS_TABLE)
                ->values([
                    'note_id' => $id,
                    'tag' => $tag
                ])
                ->execute();
        }

        return 'Note saved';
    }
}
