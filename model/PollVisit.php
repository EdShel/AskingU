<?php
require_once "classes/DbAccess.php";

class PollVisit
{
    /**
     * @var int Who has visited the poll page
     */
    public int $UserId;

    /**
     * @var int Which poll has been visited.
     */
    public int $PollId;

    /**
     * @var false|string When the poll was visited
     */
    public string $Date;

    /**
     * PollVisit constructor.
     * @param int $userId Who has visited
     * @param int $pollId What poll was visited
     * @param string|null $date When the poll was visited.
     * By default: current date
     */
    public function __construct(int $userId, int $pollId, ?string $date = NULL)
    {
        $this->UserId = $userId;
        $this->PollId = $pollId;

        if ($date) {
            $this->Date = $date;
        } else {
            $this->Date = date('Y-m-d', time());
        }
    }

    /**
     * @param DbAccess $db Increases visits counter or creates a new entry in db.
     */
    public function IncrementVisitCounter(DbAccess $db)
    {
        // Try to get this user's view entry
        $stmt = $db->PrepareStatement(
            "SELECT 1 FROM pollVisit WHERE userId = :userId AND pollId = :pollId AND dayDate = :date");
        $stmt->bindParam(":userId", $this->UserId);
        $stmt->bindParam(":pollId", $this->PollId);
        $stmt->bindParam(":date", $this->Date);
        $stmt->execute();
        $r = $stmt->fetch();

        $isInDb = 1 == $r;

        // If the user hasn't visited the poll page today
        if (!$isInDb) {

            // Add a new record
            $stmt = $db->PrepareStatement(<<<SQL
                INSERT INTO pollVisit(userId, pollId, dayDate, visits) 
                VALUES(:userId, :pollId, :dayDate, 1);
            SQL);
            $stmt->bindParam(":userId", $this->UserId);
            $stmt->bindParam(":pollId", $this->PollId);
            $stmt->bindParam(":dayDate", $this->Date);
            $stmt->execute();
        } else {

            // Update the existing one
            $stmt = $db->PrepareStatement(<<<SQL
                UPDATE pollVisit SET visits = visits + 1 
                WHERE pollId = :pollId AND userId = :userId AND dayDate = :date
            SQL);
            $stmt->bindParam(":userId", $this->UserId);
            $stmt->bindParam(":pollId", $this->PollId);
            $stmt->bindParam(":date", $this->Date);
            $stmt->execute();
        }
    }

    /**
     * @param int $pollId Which poll to check.
     * @param DbAccess $db Db connection to use
     * @return int How many unique users have visited the poll
     * today.
     */
    public static function GetUniqueVisits(int $pollId, DbAccess $db): int
    {
        $day = date('Y-m-d', time());
        $stmt = $db->PrepareStatement("SELECT COUNT(userId) FROM pollVisit WHERE pollId = :pollId AND dayDate = :day;");
        $stmt->bindParam(":pollId", $pollId);
        $stmt->bindParam(":day", $day);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_NUM)[0];
    }

    /**
     * Initializes required SQL table
     */
    public static function InitSQLTable(DbAccess $db): void
    {
        $db->SQLRun(<<<SQL
            CREATE TABLE pollVisit(
                userId INTEGER,
                pollId INTEGER,
                dayDate TEXT,
                visits INTEGER,
                PRIMARY KEY(userId, pollId, dayDate),
                FOREIGN KEY (userId) REFERENCES users(id),
                FOREIGN KEY (pollId) REFERENCES polls(id) ON DELETE CASCADE
            );
        SQL
        );
    }
}