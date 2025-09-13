DROP DATABASE IF EXISTS chatterbox;

CREATE DATABASE chatterbox
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
    
USE chatterbox;

CREATE TABLE users (
    userId INT PRIMARY KEY AUTO_INCREMENT,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    token TEXT NOT NULL
);

CREATE TABLE group_chat (
    groupId INT PRIMARY KEY AUTO_INCREMENT,
    name TEXT NOT NULL,
    creationTimeStamp INT NOT NULL
);

CREATE TABLE message (
    messageId INT PRIMARY KEY AUTO_INCREMENT,
    sender INT NOT NULL,
    receiver INT NOT NULL,
    content TEXT NOT NULL,
    timestamp INT NOT NULL,
    FOREIGN KEY (sender) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (receiver) REFERENCES group_chat(groupId) ON DELETE CASCADE
);

CREATE TABLE group_membre (
    groupId INT NOT NULL,
    userId INT NOT NULL,
    joinTimeStamp INT NOT NULL,
    PRIMARY KEY (groupId, userId),
    FOREIGN KEY (groupId) REFERENCES group_chat(groupId) ON DELETE CASCADE,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
);