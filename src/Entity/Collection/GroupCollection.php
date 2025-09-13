<?php

namespace Entity\Collection;

use Database\MyPdo;
use Entity\GroupChat;

class GroupCollection
{
    /**
     * @return GroupChat[]
     */
    public static function getAllGroups(): array
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT gc.*
                FROM group_chat gc
                JOIN group_membre gm ON gc.groupId = gm.groupId
                GROUP BY gc.groupId
                HAVING COUNT(gm.userId) > 0
            SQL
        );

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_CLASS, GroupChat::class);
    }
}
