<?php

require_once "DbAccess.php";

class Like
{
    public int $UserId;

    public int $PollId;

    public function __construct(int $userId, int $pollId)
    {
        $this->UserId = $userId;
        $this->PollId = $pollId;
    }

    public function ToDb(DbAccess $db)
    {
        $stmt = $db->PrepareStatement("INSERT INTO likes(userId, pollId) VALUES(:userId, :pollId);");
        $stmt->bindParam(":userId", $this->UserId);
        $stmt->bindParam(":pollId", $this->PollId);
        $stmt->execute();
    }

    public static function InitSQLTable(DbAccess $db): void
    {
        $db->SQLRun(<<<SQL
            CREATE TABLE likes(
                userId INTEGER,
                pollId INTEGER,
                PRIMARY KEY(userId, pollId),
                FOREIGN KEY (userId) REFERENCES users(id),
                FOREIGN KEY (pollId) REFERENCES polls(id) ON DELETE CASCADE
            );
        SQL
        );
    }
}