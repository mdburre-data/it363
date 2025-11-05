<?php
declare(strict_types=1);
namespace App;

use PDO;

class DB {
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo === null) {
            $dsn = DB_DSN;
            $user = DB_USER;
            $pass = DB_PASS;
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::migrate();
        }
        return self::$pdo;
    }

    private static function migrate(): void {
        $pdo = self::$pdo;

        // users (create minimal table if it doesn't exist)
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            email TEXT PRIMARY KEY,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // --- ensure profile columns exist (SQLite-friendly) ---
        $cols = self::tableColumns($pdo, 'users');
        if (!in_array('fname', $cols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN fname TEXT");
        }
        if (!in_array('lname', $cols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN lname TEXT");
        }
        if (!in_array('section', $cols, true)) {
            $pdo->exec("ALTER TABLE users ADD COLUMN section TEXT");
        }

        // login_codes
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL,
            code_hash TEXT NOT NULL,
            expires_at DATETIME NOT NULL,
            used_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // appointments table removed from this minimal auth-only build

        // indexes
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_login_codes_email ON login_codes(email)");
    }

    private static function tableColumns(PDO $pdo, string $table): array {
        // Works on SQLite; harmless on MySQL if you later switch (you'd adjust to INFORMATION_SCHEMA)
        $stmt = $pdo->query("PRAGMA table_info(" . $table . ")");
        $cols = [];
        foreach ($stmt ?: [] as $row) {
            if (isset($row['name'])) $cols[] = $row['name'];
        }
        return $cols;
    }
}
