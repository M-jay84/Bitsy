<?php

declare(strict_types=1);

namespace App\Libs;

use PDO;
use PDOException;

class DB
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function instance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHAR;

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                self::logError('Connection failed', $e->getMessage());
                http_response_code(500);
                exit('Database temporarily unavailable. Please try again later.');
            }
        }

        return self::$instance;
    }

    /**
     * Main method – always use this for queries
     */
    public static function run(string $sql, array $args = []): \PDOStatement
    {
        try {
            if (empty($args)) {
                return self::instance()->query($sql);
            }

            $stmt = self::instance()->prepare($sql);
            $stmt->execute($args);
            return $stmt;

        } catch (PDOException $e) {
            self::logQueryError($sql, $args, $e);
            // Re-throw so the global Errors handler can show a friendly message
            throw $e;
        }
    }

    /**
     * Keep the update helper
     */
    public static function update(string $table, array $data, array $where): int
    {
        $setParts = [];
        foreach (array_keys($data) as $col) {
            $setParts[] = "`$col` = ?";
        }
        $set = implode(', ', $setParts);

        $whereParts = [];
        foreach (array_keys($where) as $col) {
            $whereParts[] = "`$col` = ?";
        }
        $whereClause = implode(' AND ', $whereParts);

        $sql = "UPDATE `$table` SET $set WHERE $whereClause";
        $stmt = self::run($sql, array_merge(array_values($data), array_values($where)));

        return $stmt->rowCount();
    }

    /**
     * Allow normal PDO methods (query, prepare, lastInsertId, etc.)
     * while still using the single connection
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        return self::instance()->$method(...$args);
    }

    // ---------------------------------------------------------------------
    // Error logging
    // ---------------------------------------------------------------------

    private static function logQueryError(string $sql, array $args, PDOException $e): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        // Find the first file that is NOT this DB class
        $file = 'unknown';
        $line = 0;
        foreach ($trace as $t) {
            if (isset($t['file']) && !str_contains($t['file'], 'DB.php')) {
                $file = $t['file'];
                $line = $t['line'] ?? 0;
                break;
            }
        }

        // Make args safe for logging (remove very long values / binary)
        $safeArgs = array_map(static function ($value) {
            if (is_string($value) && strlen($value) > 200) {
                return substr($value, 0, 200) . '... [truncated]';
            }
            if (is_null($value)) {
                return 'NULL';
            }
            return $value;
        }, $args);

        $message = sprintf(
            "[%s] SQL ERROR\n" .
            "Message : %s\n" .
            "File    : %s:%d\n" .
            "SQL     : %s\n" .
            "Params  : %s\n" .
            "----------------------------------------\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $file,
            $line,
            $sql,
            json_encode($safeArgs, JSON_UNESCAPED_UNICODE)
        );

        error_log($message, 3, LOG_PATH . '/db_errors.log');
    }

    private static function logError(string $title, string $details): void
    {
        $message = sprintf(
            "[%s] %s\n%s\n----------------------------------------\n",
            date('Y-m-d H:i:s'),
            $title,
            $details
        );
        error_log($message, 3, LOG_PATH . '/db_errors.log');
    }

    // update = careful merge removes duplicate array key ???
    public static function updateset(string $table, array $data, array $where)
    {
        //merge data and where together
        $collection = array_merge($data, $where);
        //collect the values from collection
        $values = array_values($collection);
        //setup fields
        $fieldDetails = null;
        foreach ($data as $key => $value) {
            $fieldDetails .= "$key = ?,";
        }
        $fieldDetails = rtrim($fieldDetails, ',');
        //setup where 
        $whereDetails = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $whereDetails .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $i++;
        }
        $stmt = self::run("UPDATE $table SET $fieldDetails WHERE $whereDetails", $values);
        return $stmt->rowCount();
    }

}