<?php

namespace Src;

final class Database
{
    /**
     * @var Database $instance Our singleton instance.
     */
    private static $instance;
    /**
     * @var \PDO $db Our PDO object.
     */
    private $db;
    /**
     * @var string $query The query to execute next.
     */
    private $query;
    /**
     * @var array $params Array of ':placeholder' => $placeholder for prepared statements.
     */
    private $params;
    /**
     * @var \PDOStatement $stmt Our PDO Statement.
     */
    private $stmt;

    /**
     * Database constructor. Private so we can only access via self::getInstance
     * @param array $config The server config to use.
     * @TODO set options (& maybe db name)
     */
    private function __construct(array $config)
    {
        $this->db = new \PDO($config['server'], $config['username'], $config['password']);
    }

    /**
     * Gets the singleton instance.
     * @param array $config The server config to use.
     * @return Database Returns our singleton instance.
     */
    public static function getInstance(array $config): Database
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database($config);
        }

        return self::$instance;
    }

    /**
     * Execute our prepared statement.
     * @return bool True on success, false on failure.
     * @throws \Exception Throws if query is not set.
     */
    public function execute(): bool
    {
        if (!isset($this->query)) {
            throw new \Exception('Null query');
        }

        $this->stmt = $this->db->prepare($this->query);

        return isset($this->params) ? $this->stmt->execute($this->params) : $this->stmt->execute();
    }

    /**
     * Sets the query to execute (with named placeholders)
     * @param string $query The query.
     */
    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    /**
     * Sets params for a prepared statement.
     * @param array $params Array of ':placeholder' => $placeholder for prepared statements.
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Fetches the next result row from the last result set produced by the last execute.
     * @return array|mixed The result row as an array or false on failure.
     */
    public function getResult()
    {
        return $this->stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetches the entire result set produced by the last execute.
     * @return array|mixed The result set as an array or false on failure.
     */
    public function getResults()
    {
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Closes the connection and destroys the singleton instance.
     */
    public function closeConnection(): void
    {
        $this->stmt = null;
        $this->db = null;
        $this->params = null;
        $this->query = null;
        self::$instance = null;
    }

    /**
     * So we can't clone the singleton.
     * @throws \Exception
     */
    public function __clone()
    {
        throw new \Exception("Can't clone a singleton");
    }
}
