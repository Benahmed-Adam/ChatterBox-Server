<?php

namespace Entity\Collection;

use Database\MyPdo;
use Entity\User;

class UserCollection
{
    /**
     * @return User[]
     */
    public static function getAllUsers(): array
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM users
            SQL
        );

        $stmt->execute();

        $users = $stmt->fetchAll(\PDO::FETCH_CLASS, User::class);

        return $users;
    }

    /**
     * @return User[]
     */
    public static function getUsersByStartingName(string $startingName): array
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM users
                WHERE username LIKE :startingName
                ORDER BY username ASC
            SQL
        );

        $stmt->execute([":startingName" => $startingName . "%"]);

        $users = $stmt->fetchAll(\PDO::FETCH_CLASS, User::class);

        return $users;
    }

}
