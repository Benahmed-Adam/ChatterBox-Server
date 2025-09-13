<?php

namespace Entity;

use Database\MyPdo;
use Entity\Exception\EntityNotFoundException;
use Entity\Message;
use Entity\GroupChat;
use Entity\Exception\UserAlreadyExists;

class User
{
    public ?int $userId;
    public string $username;
    public string $password;
    public string $token;

    public function getId(): ?int
    {
        return $this->userId;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setId(?int $id): User
    {
        $this->userId = $id;
        return $this;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function setPassword(string $new_password): User
    {
        $this->password = password_hash($new_password, PASSWORD_DEFAULT);
        return $this;
    }

    public function setToken(?string $new_token): User
    {
        $this->token = $new_token;
        return $this;
    }

    public function createToken(): User
    {
        $token = bin2hex(random_bytes(32));
        $this->setToken($token);
        return $this;
    }

    public function joinGroup(int $groupId): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                INSERT IGNORE INTO group_membre (groupId, userId, joinTimeStamp)
                VALUES (:groupId, :userId, :joinTimeStamp);
            SQL
        );

        $stmt->execute([
            ":groupId" => $groupId,
            ":userId" => $this->getId(),
            ":joinTimeStamp" => time()
        ]);
    }

    public function leaveGroup(int $groupId): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                DELETE
                FROM group_membre
                WHERE groupId = :groupId and userId = :userId
            SQL
        );

        $stmt->execute([
            ":groupId" => $groupId,
            ":userId" => $this->getId()
        ]);
    }

    /**
     * @return GroupChat[]
     */
    public function getGroups(): array
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM group_chat gc
                JOIN group_membre gm ON gc.groupId = gm.groupId
                WHERE gm.userId = :userId
            SQL
        );

        $stmt->execute([":userId" => $this->getId()]);

        return $stmt->fetchAll(\PDO::FETCH_CLASS, GroupChat::class);
    }

    public function delete(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                DELETE
                FROM users
                WHERE userId = :userId
            SQL
        );

        $stmt->execute([":userId" => $this->getId()]);

        $this->setId(null);
    }

    public function insert(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                INSERT INTO users (username, password, token)
                VALUES (:username, :password, :token)
            SQL
        );

        $stmt->execute([
            ":username" => $this->getUsername(),
            ":password" => $this->getPassword(),
            ":token" => $this->getToken()
        ]);

        $this->setId((int) MyPdo::getInstance()->lastInsertId());
    }

    public function update(): void
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                UPDATE users
                SET username = :username, 
                    password = :password, 
                    token = :token
                WHERE userId = :userId
            SQL
        );

        $stmt->execute([
            ":userId" => $this->getId(),
            ":username" => $this->getUsername(),
            ":password" => $this->getPassword(),
            ":token" => $this->getToken()
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

    public function sendMessage(string $message, int $groupId): Message
    {
        return Message::create($this->getId(), $groupId, $message);
    }

    public static function create(string $username, string $password): User
    {
        try {
            $t = User::getUserByUsername($username);
            throw new UserAlreadyExists("L'utilisateur existe déjà");
        } catch (EntityNotFoundException $e) {
            $user = new User();
            $user->setId(null)->setUsername($username)->setPassword($password)->createToken();

            $user->save();

            return $user;
        }
    }

    public static function getUserById(int $id): User
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM users
                WHERE userId = :userId
            SQL
        );

        $stmt->execute([":userId" => $id]);

        $user = $stmt->fetchObject(User::class);

        if (!$user) {
            throw new EntityNotFoundException("L'utilisateur n'existe pas");
        }

        return $user;
    }

    public static function getUserByUsername(string $username): User
    {
        $stmt = MyPdo::getInstance()->prepare(
            <<<'SQL'
                SELECT *
                FROM users
                WHERE username = :username
            SQL
        );

        $stmt->execute([":username" => $username]);

        $user = $stmt->fetchObject(User::class);

        if (!$user) {
            throw new EntityNotFoundException("L'utilisateur n'existe pas");
        }

        return $user;
    }
}
