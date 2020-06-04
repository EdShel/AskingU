<?php

require_once "iModelMap.php";

class User implements iModelMap
{
    public int $Id;
    public string $Name;
    public string $Email;
    public string $Password;
    /*
     * Rights of the user.
     * If all the bits = 1, then admin.
     * [0] bit - can delete messages
     */
    public int $Claims;

    public int $YearOfBirth;

    /*
     * Gender of the user.
     * 0 - male
     * 1 - female
     */
    public int $Gender;

    public string $AccessToken;

    public function __construct()
    {

    }

    public static function FromQueryResult(array $queryResult): User
    {
        // Create new object without any filled in fields
        $user = new User();

        // Fill in the fields
        $user->Id = $queryResult["id"];
        $user->Name = $queryResult["name"];
        $user->Email = $queryResult["email"];
        $user->Password = $queryResult["password"];
        $user->Claims = $queryResult["claims"];
        $user->YearOfBirth = $queryResult["year"];
        $user->Gender = $queryResult["gender"];
        $user->AccessToken = $queryResult["accessToken"];

        return $user;
    }

    public static function GetUserIdWithAccessTokenValidation(DbAccess $db): int
    {
        $user = User::FromCookies($db);
        if ($user === NULL) {
            return -1;
        } else {
            return $user->Id;
        }
    }

    public static function FromCookies(DbAccess $db): ?User
    {
        // If there are required cookies of user id and his access token
        if (isset($_COOKIE['accessToken'])
            && isset($_COOKIE['id'])) {
            // Get this user from db
            $userId = $_COOKIE['id'];

            $userResult = $db->SQLSingle(<<<SQL
                SELECT * FROM users WHERE id = {$userId} LIMIT 1; 
            SQL, true);

            // If user exists
            if ($userResult && count($userResult) != 0) {
                // Check written access token
                $accessToken = $userResult['accessToken'];

                if ($_COOKIE['accessToken'] == $accessToken) {
                    // Parse the user from sql query result
                    return self::FromQueryResult($userResult);
                }
            }
        }

        return NULL;
    }

    public static function FromUserId(DbAccess $db, int $userId): ?User
    {
        $userResult = $db->SQLSingle(<<<SQL
                SELECT * FROM users WHERE id = {$userId} LIMIT 1; 
            SQL, true);

        if (count($userResult) == 0) {
            return NULL;
        }
        return self::FromQueryResult($userResult);
    }

    public static function GetUserIdFromCookies(): int
    {
        if (isset($_COOKIE['accessToken'])
            && isset($_COOKIE['id'])) {
            return $_COOKIE['id'];
        }
        return -1;
    }

    public static function IsUserRegistered(DbAccess $db, string $email): bool
    {
        return $db->SQLSingle(<<<SQL
            SELECT id FROM users WHERE email = "{$email}";
        SQL, FALSE) != NULL;
    }

    public function RegisterUser(DbAccess $db): void
    {
        $db->SQLSingle(<<<SQL
            INSERT INTO users(name, email, password, year, gender, claims)
            VALUES ('{$this->Name}', '{$this->Email}', '{$this->Password}',
                    '{$this->YearOfBirth}', '{$this->Gender}', 0)
        SQL, false
        );
    }

    /*
     * Generates access token to give it to the user.
     */
    public static function GenerateAccessToken()
    {
        // Range of available characters
        $possibleChars = "abcdefghijklmnopqrstuvwxyz"
            . "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
            . "0123456789";
        $posLen = strlen($possibleChars);

        // Generate random char from the given range and append it
        $token = "";
        for ($i = 0; $i < 10; $i++) {
            // If can use cryptographic generator
            try {
                // Use it
                $r = random_int(0, $posLen - 1);
            } catch (Exception $e) {
                // Not cryptographic generator
                $r = mt_rand(0, $posLen - 1);
            }

            $token .= $possibleChars[$r];
        }

        return $token;
    }

    public static function HashPassword(string $password): string
    {
        return md5(trim($password));
    }

    public static function IsEmailCorrect(string $mail): bool
    {
        return preg_match("/([\w]+\.?)+@\w+(\.[\w]+){1,3}/", $mail);
    }

    public static function IsNameCorrect(string $name, &$exception): bool
    {
        // Unicode sequence with valid cyrillic and latin characters
        $wchar = '[A-Za-zА-Яа-яЁё]';

        // Initials substring e.g. "А.С. Пушкин" => "А.С." or "А.Пушкин" => "A."
        $inits = "( ( ( ([A-Z][a-z]?) | ([А-ЯЁ][а-яё]) ) \. \s? ){1,2} )";

        // Second name substring e.g. "А.С. Пушкин" => " Пушкин" or "Дж. фон Нейман" => " фон Нейман"
        $secondName = "(\s*['`]?{$wchar}+(-{$wchar}+)?)+";

        // Check the name for initials are only at the beginning
        $correctNameAndInitials =
            (preg_match("~{$inits}?{$secondName}~xs", $name)
                // Check for trailing initials and their absence
                || preg_match("~{$secondName}\s+{$inits}?~xs", $name));

        // Check for forbidden symbols
        $containsForbiddenChars = preg_match("~[0-9!?,\"/\\\@#$%^&*()=+]~", $name);

        if (!$correctNameAndInitials) {
            $exception = "Неправильная запись фамилии и инициалов!";
            return false;
        }
        if ($containsForbiddenChars) {
            $exception = "Фамилия и инициалы не должны содержать следующие символы: цифры, знаки пунктуации, спец-символы";
            return false;
        }

        return true;
    }

    /*
     * Initializes db table of users
     */
    public static function InitSQLTable(DbAccess $db): void
    {
        $db->SQLRun(<<<SQL
            CREATE TABLE users(
                id INTEGER PRIMARY KEY,
                name TEXT,
                email TEXT UNIQUE,
                password TEXT,
                claims INTEGER,
                year INTEGER,
                gender INTEGER,
                accessToken TEXT
            );
            SQL
        );
    }
}