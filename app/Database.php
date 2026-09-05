<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $c = $GLOBALS['config']['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $c['host'],
                $c['port'],
                $c['name'],
                $c['charset']
            );
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::ensureColumns();
        }
        return self::$pdo;
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    private static function ensureColumns(): void
    {
        $adds = [
            'criteria' => [
                'section' => 'VARCHAR(180) NULL',
                'max_score' => 'DECIMAL(6,2) NOT NULL DEFAULT 100.00',
            ],
            'criteria_template_items' => [
                'section' => 'VARCHAR(180) NULL',
                'max_score' => 'DECIMAL(6,2) NOT NULL DEFAULT 100.00',
            ],
        ];
        foreach ($adds as $table => $cols) {
            foreach ($cols as $name => $def) {
                $exists = self::$pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . self::$pdo->quote($name))->fetch();
                if (!$exists) {
                    self::$pdo->exec("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$def}");
                }
            }
        }
    }
}
