# ChatterBox-Server

## Introduction

**ChatterBox-Server** constitue la logique applicative côté serveur de la plateforme **ChatterBox**.

## Table des matières

* [Introduction](#introduction)
* [Fonctionnalités](#fonctionnalités)
* [Structure du projet](#structure-du-projet)
* [Installation](#installation)
* [Utilisation](#utilisation)
* [Dépendances](#dépendances)
* [Configuration](#configuration)
* [Mise en place de la base de données](#mise-en-place-de-la-base-de-données)
* [Exemples](#exemples)
* [Contributeurs](#contributeurs)

## Fonctionnalités

* Une API REST pour les fonctionnalités de ChatterBox
* Gestion des groupes, des utilisateurs, des messages, etc...
* La configuration du schéma de base de données via `creationScript.sql`

## Structure du projet

Organisation du dépôt :

```
/bin
/public
/src
.gitignore
bootstrap.php
composer.json
composer.lock
php-cs-fixer.php
creationScript.sql
.mypdo.ini.dist
README.md
```

* **/bin** — scripts / exécutables
* **/public** — fichiers exposés au serveur web
* **/src** — logique serveur
* **bootstrap.php** — initialisation de l’environnement (autoloading)
* **composer.json** / **composer.lock** — dépendances PHP
* **creationScript.sql** — script SQL pour l’initialisation du schéma de base de données

## Installation

Étapes générales pour exécuter **ChatterBox-Server** :

1. Installer **PHP**.
2. Installer **Composer**.
3. Cloner le dépôt :

   ```bash
   git clone https://github.com/Benahmed-Adam/ChatterBox-Server.git
   cd ChatterBox-Server
   ```
4. Installer les dépendances via Composer :

   ```bash
   composer install
   ```
5. Mettre en place la base de données avec `creationScript.sql` (MySQL, PostgreSQL ou autre moteur compatible).
6. Configurer les variables d’environnement ou fichiers de configuration (identifiants de base de données, clés API, etc.) en copiant `.mypdo.ini.dist` vers `.mypdo.ini`, puis en y ajoutant vos identifiants.
7. S’assurer que le répertoire `public` est desservi par un serveur web (**Apache**, **Nginx**) ou via le serveur intégré de PHP pour le développement.

## Utilisation

Pour lancer l’application avec le serveur intégré de PHP :

```bash
composer start:linux   # sous Linux
composer start:windows # sous Windows
```

## Dépendances

* **PHP**
* Paquets Composer définis dans `composer.json`
* Base de données SQL (en fonction de la configuration)
* **PHP-CS-Fixer** (pour le développement)

## Configuration

Les éléments suivants doivent être configurés :

* Connexion à la base de données (hôte, port, utilisateur, mot de passe, nom de la base) via `.mypdo.ini`
* Racine du serveur web pointant vers le répertoire `public`

## Mise en place de la base de données

Utiliser le script fourni `creationScript.sql` pour initialiser le schéma de base de données. Exemple :

```sql
source creationScript.sql;
```

Adapter ensuite les identifiants de connexion dans le fichier de configuration.

## Exemples

Voir le dépôt principal [ChatterBox](https://github.com/Benahmed-Adam/ChatterBox).

## Contributeurs

* **Benahmed-Adam**