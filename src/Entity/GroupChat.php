<?php

namespace Entity;

use Database\MyPdo;
use Entity\User;
use Entity\Message;
use Entity\Exception\EntityNotFoundException;

class GroupChat
{
    public ?int $groupId;
    public string $name;
    public int $creationTimeStamp;

    public function getId(): ?int
    {
        return $this->groupId;
    }

    public function setId(?int $groupId): GroupChat
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GroupChat
    {
        $this->name = $name;
        return $this;
    }

    public function getCreationTimeStamp(): int
    {
        return $this->creationTimeStamp;
    }

    public function setCreationTimeStamp(int $creationTimeStamp): GroupChat
    {
        $this->creationTimeStamp = $creationTimeStamp;
        return $this;
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT u.userId, u.username, u.password, u.token
                FROM users u
                JOIN group_membre gm ON u.userId = gm.userId
                WHERE gm.groupId = :groupId
            SQL
        );

        $stmt->execute([":groupId" => $this->getId()]);

        $users = $stmt->fetchAll(\PDO::FETCH_CLASS, User::class);

        return $users;
    }

    public function delete(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                DELETE
                FROM group_chat
                WHERE groupId = :groupId
            SQL
        );

        $stmt->execute([":groupId" => $this->getId()]);

        $this->setId(null);
    }
    public function insert(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                INSERT INTO group_chat (name, creationTimeStamp)
                VALUES (:name, :creationTimeStamp)
            SQL
        );

        $stmt->execute([
            ":name" => $this->getName(),
            ":creationTimeStamp" => $this->getCreationTimeStamp(),
        ]);

        $this->setId((int) MyPdo::getInstance()->lastInsertId());
    }

    public function update(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                UPDATE group_chat
                SET name = :name
                WHERE groupId = :groupId
            SQL
        );

        $stmt->execute([
            ":groupId" => $this->getId(),
            ":name" => $this->getName(),
        ]);
    }

    public function save(): void
    {
        if ($this->getId() === null) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    /**
     * @return Message[]
     */
    public function getAllMessages(int $limit, int $offset): array
    {
        $limit = (int) $limit;
        $offset = (int) $offset;

        $sql = <<<SQL
            SELECT *
            FROM message
            WHERE receiver = :groupId
            ORDER BY messageId DESC
            LIMIT $limit OFFSET $offset
        SQL;

        $stmt = MyPdo::getInstance()->prepare($sql);

        $stmt->execute([
            ":groupId" => $this->getId(),
        ]);

        $messages = $stmt->fetchAll(\PDO::FETCH_CLASS, Message::class);

        return $messages;
    }

    public static function create(string $name): GroupChat
    {
        $group = new GroupChat();
        $group->setId(null)->setName($name)->setCreationTimeStamp(time());
        $group->save();

        return $group;
    }

    public static function getGroupById(int $groupId): GroupChat
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM group_chat
                WHERE groupId = :groupId
            SQL
        );

        $stmt->execute([":groupId" => $groupId]);

        $group = $stmt->fetchObject(GroupChat::class);

        if (!$group) {
            throw new EntityNotFoundException("Le groupe n'existe pas");
        }

        return $group;
    }

    public static function purgeEmptyGroups(): int
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
            DELETE FROM group_chat
            WHERE groupId NOT IN (
                SELECT DISTINCT gm.groupId
                FROM group_membre gm
            )
            SQL
        );

        $stmt->execute();
        return $stmt->rowCount();
    }
}
