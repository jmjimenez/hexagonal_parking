<?php

namespace Jmj\Test\Unit\Application\Command\Handler\InMemory\Pdo;

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
}
