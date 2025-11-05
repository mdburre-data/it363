<?php
declare(strict_types=1);
namespace App;

use PDO;
use PDOException;

class DB {
    private static ?PDO $pdo = null;

    public static function conn(): PDO {
        if (self::$pdo === null) {
            $dsn = DB_DSN;
            $user = DB_USER;
            $pass = DB_PASS;

            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                die('Database connection failed: ' . $e->getMessage());
            }

         //   self::migrate();
        }
        return self::$pdo;
    }

}
