<?php

require_once "Poll.php";
require_once "Variant.php";
require_once "Vote.php";
require_once "User.php";
require_once "ErrorHandler.php";

class DbAccess
{
    // Path to the database
    protected static string $dbLocation = "polls.db";

    // Inner database connection instance
    protected PDO $db;

    /*
     * Constructor to open database connection and
     * configure db connection instance
     */
    function __construct()
    {
        $dbExists = file_exists(self::$dbLocation);
        //$this->db = new SQLite3(self::$dbLocation);
        try {
            $this->db = new PDO("sqlite:" . self::$dbLocation);
        } catch (PDOException $ex) {
            die ('Cannot open data base connection!');
        }

        // Db object must throw exceptions (in order to handle them)
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Enable foreign keys constraints
        $this->SQLRun("PRAGMA foreign_keys = ON;");

        // Perform first initialization
        if (!$dbExists) {
            try {
                self::InitializeDb();
            } catch (Exception $ex) {
                ErrorHandler::AddError(
                    "Не удалось инициализировать базу данных!\n"
                    . $ex->getMessage());
            }
        }
    }

    public function SQLTransaction(string $sql): bool
    {
        try {
            $this->db->beginTransaction();

            $this->db->exec($sql);

            $this->db->commit();

            return true;
        } catch (PDOException $ex) {
            $info = $this->db->errorInfo();
            $code = $this->db->errorCode(); // the same as $info[0]

            ErrorHandler::AddError("Не получилось выполнить транзакцию \n$sql"
            . "Код ошибки SQLSTATE: $code, код ошибки драйвера: $info[1]\nИнформация: $info[2]");
            $this->db->rollBack();

            return false;
        }
    }

    /*
     * Runs all the sql queries written in string.
     */
    public function SQLRun(string $sql): void
    {

        // Split SQL query onto separate commands by semicolon
        preg_match_all("/.+?;/ms", $sql, $queries);

        // Execute each query individually
        foreach ($queries[0] as $key => $query) {
            try {
                $this->db->exec($query);
            } catch (Exception $ex) {
                ErrorHandler::AddError(
                    "Не удалось выполнить подзапрос {$query}\n"
                    . $ex->getMessage());
            }
        }
    }

    /*
     * If entireRow = false: returns the first column or NULL;
     * If entireRow = true: returns an array of columns or an empty array.
     */
    public function SQLSingle(string $sql, bool $entireRow)
    {
        try {

            $result = $this->db->query($sql);
            if ($entireRow) {
                return $result->fetch();
            }

            return $result->fetchColumn();

        } catch (Exception $ex) {
            ErrorHandler::AddError(
                "Не получилось выполнить запрос: $sql\n"
                . $ex->getMessage());

            return NULL;
        }
    }

    /*
     * Returns an array of results or NULL if can't retrieve data.
     */
    public function SQLMultiple(string $sql): ?PDOStatement
    {
        try {
            $result = $this->db->query($sql);
            if ($result === FALSE) {
                return NULL;
            }

            return $result;
        } catch (Exception $ex) {
            ErrorHandler::AddError(
                "Не получилось выполнить запрос: $sql\n"
                . $ex->getMessage());
        }
        return NULL;
    }

    /*
     * Returns row Id of the last inserted item into db or -1.
     */
    public function GetLastInsertId(): int
    {
        try {
            return $this->db->lastInsertId();
        } catch (Exception $ex) {
            ErrorHandler::AddError(
                "Не получилось достать последний добавленный элемент!"
                . $ex->getMessage());
        }
        return -1;
    }


    /*
     * Performs first initialization of the DB
     */
    private function InitializeDb()
    {
        Poll::InitSQLTable($this);
        Variant::InitSQLTable($this);
        User::InitSQLTable($this);
        Vote::InitSQLTable($this);
    }
}