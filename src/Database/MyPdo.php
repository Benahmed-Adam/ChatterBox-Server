<?php

declare(strict_types=1);

namespace Database;

/**
 * Classe permettant de retourner une instance unique et configurée de PDO.
 *
 * Ceci permet de ne pas multiplier les connexions au serveur de base de données.
 * L'instance peut être configurée de trois façons, utilisées dans cet ordre jusqu'à obtenir une configuration valide :
 *  - programmatique ; MyPDO::setConfiguration(DSN, username, password)
 *  - variables d'environnement ; MYPDO_DSN, MYPDO_USERNAME et MYPDO_PASSWORD
 *  - fichier ; [APP_DIR/].mypdo[.MYPDO_ENV].ini où APP_DIR et MYPDO_ENV sont des variables d'environnement
 *
 * @startuml
 *
 *  namespace Database {
 *      class MyPdo {
 *          - {static} dsn : string
 *          - {static} username : string := ''
 *          - {static} password : string := ''
 *          - {static} options : array := []
 *
 *          - __construct(\n\tdsn : string,\n\tusername : string := null,\n\tpassword : string := null,\n\toptions : array := null)
 *          - private __clone() : void
 *          + {static} getInstance() : MyPdo
 *          + {static} setConfiguration(\n\tdsn : string,\n\tusername : string := '',\n\tpassword : string := '',\n\toptions : array := []) : void
 *          - {static} hasConfiguration() : bool
 *          - {static} setConfigurationFromEnvironmentVariables() : bool
 *          - {static} setConfigurationFromIniFile() : bool
 *      }
 *  }
 *
 *  Database\\MyPdo -left-|> PDO
 *  Database\\MyPdo "1" *-- "1\n-<u>myPdoInstance</u>" Database\\MyPdo : contains
 *
 * @enduml
 */
final class MyPdo extends \PDO
{
    /**
     * Instance unique de PDO.
     */
    private static self $myPdoInstance;

    /**
     *  DSN pour la connexion BD.
     */
    private static string $dsn;

    /**
     * Nom d'utilisateur pour la connexion BD.
     */
    private static string $username = '';

    /**
     * Mot de passe pour la connexion BD.
     */
    private static string $password = '';

    /**
     * Options du pilote BD.
     */
    private static array $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    ];

    /**
     * Constructeur privé.
     *
     * Seule la classe MyPDO peut construire une instance de MyPDO.
     *
     * @param string      $dsn      DSN pour la connexion BD
     * @param string|null $username Utilisateur pour la connexion BD
     * @param string|null $password Mot de passe pour la connexion BD
     * @param array|null  $options  Options du pilote BD
     */
    private function __construct(string $dsn, string $username = null, string $password = null, array $options = null)
    {
        parent::__construct($dsn, $username, $password, $options);
        // La base de données est-elle de type SQLite
        if ('sqlite' === $this->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            // Activer les clés étrangères qui sont désactivées par défaut
            $this->exec('PRAGMA foreign_keys = ON');
        }
    }

    /**
     * Empêcher le clonage, le singleton doit rester unique.
     */
    private function __clone(): void
    {
    }

    /**
     * Point d'accès à l'instance unique.
     *
     * L'instance est créée au premier appel et réutilisée aux appels suivants.
     *
     * @return self Instance unique de MyPdo
     *
     * @throws \PDOException Si la configuration n'a pas été effectuée
     */
    public static function getInstance(): self
    {
        // Instance de la classe présente ?
        if (!isset(self::$myPdoInstance)) {
            // Configuration effectuée ?
            if (!self::hasConfiguration()
                && !self::setConfigurationFromEnvironmentVariables()
                && !self::setConfigurationFromIniFile()) {
                throw new \PDOException(__CLASS__.': Configuration not set');
            }
            // Construire une instance
            self::$myPdoInstance = new self(self::$dsn, self::$username, self::$password, self::$options);
        }

        return self::$myPdoInstance;
    }

    /**
     * Fixer programmatiquement la configuration de la connexion à la BD.
     *
     * @param string $dsn      DSN pour la connexion BD
     * @param string $username Utilisateur pour la connexion BD
     * @param string $password Mot de passe pour la connexion BD
     * @param array  $options  Options du pilote BD
     *
     * @throws \PDOException Si la variable d'environnement APP_DIR est utilisée, mais n'est pas définie
     */
    public static function setConfiguration(
        string $dsn,
        string $username = '',
        string $password = '',
        array $options = []
    ): void {
        self::$dsn = $dsn;
        self::$username = $username;
        self::$password = $password;
        self::$options = $options + self::$options;

        // Remplacer %APP_DIR% par le chemin de l'application si SQLite est utilisé
        if (preg_match('/^(.*)(%APP_DIR%)(.*)$/', $dsn, $matches)) {
            if (!($appDir = getenv('APP_DIR'))) {
                throw new \PDOException(__CLASS__.': APP_DIR environment variable not set');
            }
            self::$dsn = $matches[1].$appDir.$matches[3];
        }
    }

    /**
     * Vérifier si la configuration de la connexion à la BD a été effectuée.
     */
    private static function hasConfiguration(): bool
    {
        return isset(self::$dsn);
    }

    /**
     * Lire la configuration depuis des variables d'environnement.
     *
     * Les variables sont :
     *  - MYPDO_DSN pour le DSN
     *  - MYPDO_USERNAME pour le nom d'utilisateur
     *  - MYPDO_PASSWORD pour le mot de passe.
     *
     * @return bool Vrai si la configuration a été trouvée
     *
     * @throws \PDOException Si self::setConfiguration() échoue
     */
    private static function setConfigurationFromEnvironmentVariables(): bool
    {
        // DSN ?
        $dsn = getenv('MYPDO_DSN', true);
        if (false !== $dsn) {
            // username et password facultatifs
            $username = getenv('MYPDO_USERNAME', true) ?: '';
            $password = getenv('MYPDO_PASSWORD', true) ?: '';
            self::setConfiguration($dsn, $username, $password);

            return true;
        }

        return false;
    }

    /**
     * Lire la configuration depuis un fichier ini.
     *
     * Le nom du fichier peut être :
     *  - ".mypdo.ini"
     *  - ".mypdo<.environment_name>.ini" (environment_name dans la variable d'environnement MYPDO_ENV)
     * Le fichier peut être placé :
     *  - dans un répertoire accessible (https://www.php.net/manual/fr/ini.core.php#ini.include-path)
     *  - dans le répertoire défini par la variable d'environnement APP_DIR
     * Le fichier contient :
     * [mypdo]
     * dsn = ...
     * username = ...
     * password = ...
     *
     * @return bool Vrai si la configuration a été trouvée
     *
     * @throws \PDOException Si le fichier des paramètres est invalide
     */
    private static function setConfigurationFromIniFile(): bool
    {
        // Environnement MyPdo défini ?
        $myPdoEnv = getenv('MYPDO_ENV', true) ?: '';
        // Chemin du fichier en fonction de APP_DIR
        $appDir = getenv('APP_DIR');
        $directory = false !== $appDir ? $appDir.DIRECTORY_SEPARATOR : '';
        $parameterFile = sprintf('%s.mypdo%s.ini', $directory, $myPdoEnv ? ".$myPdoEnv" : '');
        // Lecture du fichier de configuration
        $parameters = @parse_ini_file($parameterFile, true);
        if (false !== $parameters) {
            if (!isset($parameters['mypdo'])) {
                throw new \PDOException('`mypdo` section not found in `'.basename($parameterFile).'`');
            }
            if (!isset($parameters['mypdo']['dsn'])) {
                throw new \PDOException('`dsn` not found in `'.basename($parameterFile).'`');
            }
            $dsn = $parameters['mypdo']['dsn'];
            // username et password facultatifs
            $username = $parameters['mypdo']['username'] ?? '';
            $password = $parameters['mypdo']['password'] ?? '';
            self::setConfiguration($dsn, $username, $password);

            return true;
        }

        return false;
    }
}
