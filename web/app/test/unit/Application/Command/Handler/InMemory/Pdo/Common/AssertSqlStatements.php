<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo\Common;

trait AssertSqlStatements
{
    abstract public static function assertRegExp(string $pattern, string $string, string $message = ''): void;

    protected function assertUpdate(string $sqlStatement, string $table, array $conditions, array $updates = []): void
    {
        $this->assertRegExp("/^UPDATE {$table} SET .*$/", $sqlStatement);

        foreach ($conditions as $field => $condition) {
            $this->assertRegExp("/^.* WHERE .*`{$field}` = '{$condition}' .*$/", $sqlStatement);
        }

        foreach ($updates as $field => $update) {
            $this->assertRegExp("/^.* SET .*`{$field}` = '{$update}' .*$/", $sqlStatement);
        }
    }

    protected function assertInsert(string $sqlStatement, string $table, array $values)
    {
        $this->assertRegExp("/^INSERT INTO {$table} (.*) VALUES (.*)$/", $sqlStatement);

        foreach ($values as $field => $value) {
            $value = $this->normalizeString($value);
            $this->assertRegExp("/^INSERT INTO {$table} (.*`{$field}`.*) VALUES (.*'{$value}'.*)$/", $sqlStatement);
        }
    }

    protected function assertDelete(string $sqlStatement, string $table, array $values)
    {
        $this->assertRegExp("/^DELETE FROM {$table} WHERE .*$/", $sqlStatement);

        foreach ($values as $field => $value) {
            $value = $this->normalizeString($value);
            $this->assertRegExp("/^DELETE FROM {$table} WHERE .*`{$field}` = '{$value}'.*$/", $sqlStatement);
        }
    }

    protected function normalizeString(string $value): string
    {
        return str_replace('\\', '\\\\', $value);
    }
}
