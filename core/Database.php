<?php
/**
 * Conexion PDO
 */

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_NAME,
                    DB_CHARSET
                );

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
                ]);
            } catch (PDOException $exception) {
                error_log('Error de conexion DB: ' . $exception->getMessage());
                throw new Exception('No fue posible conectar a la base de datos.');
            }
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $statement = self::getInstance()->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchColumn(string $sql, array $params = [], int $column = 0)
    {
        return self::query($sql, $params)->fetchColumn($column);
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            $placeholders
        );

        self::query($sql, array_values($data));
        return (int) self::getInstance()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $params = []): int
    {
        $set = implode(', ', array_map(static fn(string $column): string => $column . ' = ?', array_keys($data)));
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $table, $set, $where);

        return self::query($sql, array_merge(array_values($data), $params))->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = sprintf('DELETE FROM `%s` WHERE %s', $table, $where);
        return self::query($sql, $params)->rowCount();
    }

    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getInstance()->rollBack();
    }

    public static function exists(string $table, string $where, array $params = []): bool
    {
        $sql = sprintf('SELECT 1 FROM `%s` WHERE %s LIMIT 1', $table, $where);
        return self::fetchOne($sql, $params) !== null;
    }

    public static function tableExists(string $table): bool
    {
        return self::fetchOne(
            "SELECT 1
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = ?
             LIMIT 1",
            [$table]
        ) !== null;
    }

    public static function columnExists(string $table, string $column): bool
    {
        return self::fetchOne(
            "SELECT 1
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?
             LIMIT 1",
            [$table, $column]
        ) !== null;
    }

    public static function getColumnMetadata(string $table, string $column): ?array
    {
        return self::fetchOne(
            "SELECT column_type,
                    is_nullable,
                    column_default
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND column_name = ?
             LIMIT 1",
            [$table, $column]
        );
    }
}
