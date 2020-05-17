<?php

require_once "DbAccess.php";
require_once "iModelMap.php";

class Variant implements iModelMap
{
    // Id of the poll to which the variant belongs
    public int $PollId;

    // Id of the answer variant
    public int $Id;

    // Text message of the answer option
    public string $Value;

    // How many votes for this variant
    public int $VotesCount = 0;

    // Whether the current user has chosen this option
    public bool $IsCurrentUsersVote = false;

    // Reference to the poll object
    public ?Poll $Poll;

    // Basic constructor
    public function __construct(int $pollId, int $variantId, string $val, int $votes = 0, ?Poll $poll = NULL)
    {
        $this->PollId = $pollId;
        $this->Id = $variantId;
        $this->Value = $val;
        $this->VotesCount = $votes;
        $this->Poll = $poll;
    }

    public function ToDB(DbAccess $db){
        $db->SQLSingle(
            "INSERT INTO variants(pollId, id, value) VALUES({$this->PollId}, {$this->Id}, \"{$this->Value}\");", false);
    }

    public static function FromQuery(array $queryResult): Variant
    {
        return new Variant($queryResult['pollId'], $queryResult['id'], $queryResult['value']);
    }

    public function GetVotesCount(DbAccess $db): ?INT
    {
        return $db->SQLSingle(<<<SQL
        SELECT COUNT(*) AS votesCount
        FROM votes
        WHERE pollId = {$this->PollId} AND variantId = {$this->Id};
        SQL, false);
    }

    public static function InitSQLTable(DbAccess $db): void
    {
        $db->SQLRun(<<<SQL
            CREATE TABLE variants(
                    pollId INTEGER,
                    id INTEGER,
                    value TEXT,
                    PRIMARY KEY(pollId, id),
                    FOREIGN KEY (pollId) REFERENCES polls(id) ON DELETE CASCADE 
                );
            SQL
        );
    }
}