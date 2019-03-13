<?php

namespace Jmj\Parking\Common\Pdo;

use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use PDO;
use PDOException;
use PDOStatement;

class PdoProxy
{
    const MYSQL = 1;
    const SQLITE = 2;

    private $connection = self::MYSQL;

    /** @var string  */
    private $dbname = 'test';

    /** @var PDO */
    private $pdo;

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $dbname
     * @throws PdoConnectionError
     */
    public function connectToMysql(string $host, string $user, string $password, string $dbname)
    {
        $this->connection = self::MYSQL;
        $this->dbname = $dbname;

        try {
            $this->pdo = new PDO(
                sprintf("mysql:host=%s;dbname=%s", $host, $this->dbname),
                $user,
                $password
            );
        } catch (PDOException $e) {
            throw new PdoConnectionError($e->getMessage());
        }
    }

    /**
     * @param $dbName
     * @throws PdoConnectionError
     */
    public function connectToSqlite($dbName)
    {
        $this->connection = self::SQLITE;
        $this->dbname = null;

        try {
            $this->pdo = new PDO("sqlite:{$dbName}");
        } catch (PDOException $e) {
            throw new PdoConnectionError($e->getMessage());
        }
    }

    /**
     * @param string $tableName
     * @param array $fields
     * @param string|null $engine
     * @return int
     * @throws PdoExecuteError
     */
    public function createTable(string $tableName, array $fields, string $engine = null) : int
    {
        $tableName = is_null($this->dbname) ? "`{$tableName}`" : "`{$this->dbname}`.`{$tableName}`";
        $engine = is_null($engine) ? '' : "ENGINE {$engine}";
        $fields = join(',', $fields);

        $sql = "CREATE TABLE {$tableName} ({$fields}) {$engine}";
        return $this->executeSql($sql);
    }

    /**
     * @param string $table
     * @param array $values
     * @return int
     * @throws \Exception
     */
    public function insert(string $table, array $values): int
    {
        $fieldNames = join(
            ',',
            array_map(
                function (string $v) {
                    return "`{$v}`";
                },
                array_keys($values)
            )
        );

        $params = [];
        foreach ($values as $field => $value) {
            $params[":{$field}"] = $value;
        }
        $paramKeys = join(',', array_keys($params));

        $sql = "INSERT INTO {$table} ({$fieldNames}) VALUES ($paramKeys)";

        return $this->execute($sql, $params);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array|bool
     * @throws PdoExecuteError
     */
    public function fetchOne(string $sql, array $params = null)
    {
        $statement = $this->prepareStatement($sql);
        $this->executeStatement($statement, $params);
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return array
     * @throws PdoExecuteError
     */
    public function fetchAll(string $sql, array $params = null) : array
    {
        $statement = $this->prepareStatement($sql);
        $this->executeStatement($statement, $params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int
     * @throws \Exception
     */
    public function execute(string $sql, array $params = null) : int
    {
        $statement = $this->prepareStatement($sql);
        $this->executeStatement($statement, $params);
        return $statement->rowCount();
    }

    /**
     * @return bool
     */
    public function startTransacction() : bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commitTransaction() : bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollbackTransaction()
    {
        return $this->pdo->rollBack();
    }

    /**
     * @return array
     */
    public function errorInfo() : array
    {
        return $this->pdo->errorInfo();
    }

    /**
     * @param string $sql
     * @return PDOStatement
     * @throws PdoExecuteError
     */
    protected function prepareStatement(string $sql): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);

        if (!$statement instanceof PDOStatement) {
            throw new PdoExecuteError("Error preparing statement {$sql}");
        }

        return $statement;
    }

    /**
     * @param PDOStatement $statement
     * @param array|null $params
     * @return bool
     * @throws PdoExecuteError
     */
    protected function executeStatement(PDOStatement $statement, array $params = null)
    {
        //TODO: it would be nice to have a hook here to publish the executed sql
        $result = $statement->execute($params);

        if (in_array($statement->errorCode(), [ '00000' ]) === false) {
            throw new PdoExecuteError($statement->errorInfo()[2]);
        }

        return $result;
    }

    /**
     * @param string $sql
     * @return int
     * @throws PdoExecuteError
     */
    protected function executeSql(string $sql) : int
    {
        try {
            //TODO: it would be nice to have a hook here to publish the executed sql
            $result = $this->pdo->exec($sql);

            $errorInfo = $this->pdo->errorInfo();

            if (in_array($errorInfo[0], [ '00000', '01000' ]) === false) {
                throw new PdoExecuteError($errorInfo[2]);
            }
        } catch (PDOException $e) {
            throw new PdoExecuteError($e->getMessage());
        }

        return $result;
    }
}
