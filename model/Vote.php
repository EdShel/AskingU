<?php

require_once "iModelMap.php";

class Vote implements iModelMap
{
    public int $VoterId;
    public int $PollId;
    public int $VariantId;

    public function __construct(array $queryResult)
    {
        $this->VoterId = $queryResult['voterId'];
        $this->PollId = $queryResult['pollId'];
        $this->VariantId = $queryResult['variantId'];
    }

    public static function InitSQLTable(DbAccess $db): void
    {
        $db->SQLRun(<<<SQL
            CREATE TABLE votes(
                voterId INTEGER,
                pollId INTEGER,
                variantId INTEGER,
                PRIMARY KEY(voterId, pollId),
                FOREIGN KEY (voterId) REFERENCES users(id),
                FOREIGN KEY (pollId) REFERENCES polls(id) ON DELETE CASCADE
            );
        SQL);
    }
}