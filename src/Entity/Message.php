<?php

namespace Entity;

use Database\MyPdo;
use Entity\User;

class Message
{
    public ?int $messageId;
    public int $sender;
    public int $receiver;
    public string $content;
    public int $timestamp;

    public function getId(): ?int
    {
        return $this->messageId;
    }

    public function setId(?int $messageId): Message
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getSender(): int
    {
        return $this->sender;
    }

    public function setSender(int $sender): Message
    {
        $this->sender = $sender;
        return $this;
    }

    public function getReceiver(): int
    {
        return $this->receiver;
    }

    public function setReceiver(int $receiver): Message
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): Message
    {
        $this->content = $content;
        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): Message
    {
        $this->timestamp = $timestamp;
        return $this;
    }

    public function delete(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                DELETE
                FROM message
                WHERE messageId = :messageId
            SQL
        );

        $stmt->execute([":messageId" => $this->getId()]);

        $this->setId(null);
    }

    public function getUser(): User
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM users u
                JOIN message m ON u.userId = m.sender
                WHERE messageId = :messageId
            SQL
        );

        $stmt->execute([":messageId" => $this->getId()]);

        $user = $stmt->fetchObject(User::class);

        if ($user === false) {
            $u = new User();
            $u->setId(-1)->setUsername("N'existe pas")->setPassword("none")->setToken("none");
            return $u;
        }
        return $user;
    }

    public function insert(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                INSERT INTO message (sender, receiver, content, timestamp)
                VALUES (:sender, :receiver, :content, :timestamp)
            SQL
        );

        $stmt->execute([
            ":sender" => $this->getSender(),
            ":receiver" => $this->getReceiver(),
            ":content" => $this->getContent(),
            ":timestamp" => $this->getTimestamp(),
        ]);

        $this->setId((int) MyPdo::getInstance()->lastInsertId());
    }

    public static function create(int $sender, int $receiver, string $content): Message
    {
        $message = new Message();
        $message->setId(null)->setSender($sender)->setReceiver($receiver)->setContent($content)->setTimestamp(time());
        $message->insert();
        return $message;
    }
}
