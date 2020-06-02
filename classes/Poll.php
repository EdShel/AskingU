<?php

require_once "DbAccess.php";
require_once "iModelMap.php";
require_once "User.php";

class Poll implements iModelMap
{
    // Id of the poll
    public int $Id;

    // Id of the user who had created this poll
    public int $CreatorId;

    // Text message of the poll question
    public string $Question;

    // How many users have answered
    public int $VotesCount = 0;

    // All the answer variants of the poll
    public array $Variants = array();

    // Whether this poll must be seen on the main page
    public bool $IsPublic;

    // Unique address of the poll
    public ?string $Url;

    // When to stop voting
    public ?DateTime $BlockingTime;

    public int $Likes;

    public bool $ShuffleVariants;

    public bool $CanVote;

    public bool $IsPollOfCurrentUser;

    // Basic constructor
    public function __construct(
        int $id,
        int $creatorId,
        string $question,
        bool $isPublic,
        ?string $url,
        ?DateTime $blockingTime,
        int $likes,
        bool $shuffleVariants)
    {
        $this->Id = $id;
        $this->CreatorId = $creatorId;
        $this->Question = $question;
        $this->IsPublic = $isPublic;
        $this->Url = $url;
        $this->BlockingTime = $blockingTime;
        $this->Likes = $likes;
        $this->ShuffleVariants = $shuffleVariants;

        $currentUser = User::GetUserIdFromCookies();
        $this->CanVote = $currentUser != -1
            && ($blockingTime == NULL || $blockingTime < new DateTime());

        $this->IsPollOfCurrentUser = ($this->CreatorId == $currentUser);
    }

    public static function FromXML(string $xml): Poll
    {
        // Check for <poll> tag
        $pollRegex = "~<poll(.*?)>(.*?)</poll>~s";
        if (!preg_match($pollRegex, $xml, $m)) {
            throw new Exception("XML должен иметь открывающий тег <poll> и закрывающий </poll>!");
        }
        $pollAttr = $m[1];
        $variants = $m[2];

        // Check for question attribute
        $questionRegex = "~question\s*=\s*([\"'])(.*?)(\\1)~s";
        if (!preg_match($questionRegex, $pollAttr, $m)) {
            throw new Exception("Тег poll должен иметь атрибут question");
        }
        $question = $m[2];

        $poll = new Poll(0, User::GetUserIdFromCookies(), $question, true, NULL, NULL, 0, false);

        // Check for variants
        $variantsRegex = "~<variant.*?>(.*?)</variant>~s";
        if (!preg_match_all($variantsRegex, $variants, $m)){
            throw new Exception("Тег <poll> должен иметь минимум 1 тег <variant>.");
        }
        foreach ($m[1] as $i => $variantText){
            $poll->Variants[] = new Variant(0, $i, $variantText);
        }

        return $poll;
    }

    /*
     * Puts poll to the database
     */
    public function ToDB(DbAccess $db)
    {
        // Get string format of date to block voting
        $blockingDateStr = self::GetDateTimeFormat($this->BlockingTime);
        // Whether this poll is visible for everyone
        $isPublicInt = (int)$this->IsPublic;
        // Whether variants must be randomly shuffled
        $shuffle = (int)$this->ShuffleVariants;
        echo "id: $this->Id ques: $this->Question isPublic: $this->IsPublic Url: $this->Url Block: $blockingDateStr likes: $this->Likes shuffle $shuffle";

        // Generate unique URL
        $this->Url = self::GenerateUniqueUrl($db, 5);

        // Add poll to the db
        $insert = <<<SQL
            INSERT INTO polls(creatorId, question, isPublic, url,
                    blockingTime, likes, shuffle) 
                    VALUES(:CreatorId, :Question, :IsPublicInt, :Url,
                    :blockingDateStr, :Likes, :Shuffle);
SQL;
        $stmt = $db->PrepareStatement($insert);

        $stmt->bindParam(":CreatorId", $this->CreatorId, PDO::PARAM_INT);
        $stmt->bindParam(":Question", $this->Question, PDO::PARAM_STR);
        $stmt->bindParam(":IsPublicInt", $isPublicInt, PDO::PARAM_INT);
        $stmt->bindParam(":Url", $this->Url, PDO::PARAM_STR);
        $stmt->bindParam(":blockingDateStr", $blockingDateStr, PDO::PARAM_INT);
        $stmt->bindParam(":Likes", $this->Likes, PDO::PARAM_INT);
        $stmt->bindParam(":Shuffle", $shuffle, PDO::PARAM_INT);

        $stmt->execute();

        // Update instance's id
        $lastRowId = $db->GetLastInsertId();
        $this->Id = $db->SQLSingle("SELECT id FROM polls WHERE rowid = {$lastRowId}", false);
    }

    /*
     * Reads poll from the database
     */
    public static function FromDb(DbAccess $db, $queryResult, int $userId, bool $withVariants): Poll
    {
        // Create new Poll from query result
        $poll = new Poll(
            $queryResult["id"],
            $queryResult["creatorId"],
            $queryResult["question"],
            $queryResult["isPublic"] ?? TRUE,
            $queryResult["url"],
            self::GetDateTime($queryResult["blockingTime"]),
            $queryResult["likes"] ?? 0,
            $queryResult["shuffle"] ?? FALSE
        );

        if ($withVariants) {
            $variantsResult = $db->SQLMultiple(<<<SQL
            SELECT pollId, id, value 
            FROM variants 
            WHERE pollId = {$poll->Id} 
            ORDER BY id;
            SQL
            );

            // If user exists
            if ($userId > 0) {
                // Find its favourite variant
                $userAnswerVariant = $poll->GetUserVoteVariantId($db, $userId);
            } else {
                // Else it's not an existing variant
                $userAnswerVariant = -1;
            }

            while ($variant = $variantsResult->fetch()) {
                $var = Variant::FromQuery($variant);

                // Find count of votes
                $var->VotesCount = $var->GetVotesCount($db);

                // Increase poll's votes count
                $poll->VotesCount += $var->VotesCount;

                // Find whether this variant is the answer of the current user
                $var->IsCurrentUsersVote = ($var->Id === $userAnswerVariant);

                // Set for this object a parent
                $var->Poll = $poll;

                // Add to the array of variants
                $poll->Variants[] = $var;
            }
        }

        return $poll;
    }

    /*
     * Deletes poll from the database
     */
    public static function DeleteFromDb(DbAccess $db, int $pollId): void
    {
        $db->SQLTransaction("DELETE FROM polls WHERE id = $pollId;");
    }

    public function GetUserVoteVariantId(DbAccess $db, int $userId): ?INT
    {
        return $db->SQLSingle(<<<SQL
        SELECT variantId
        FROM votes
        WHERE pollId = {$this->Id} AND voterId = {$userId};
        SQL, false);
    }

    /*
     * Initializes db table of polls
     */
    public static function InitSQLTable(DbAccess $db): void
    {
        $db->SQLRun(<<<SQL
            CREATE TABLE polls(
                id INTEGER PRIMARY KEY,
                creatorId INTEGER,
                question TEXT,
                isPublic BOOLEAN,
                url TEXT UNIQUE,
                blockingTime TEXT,
                likes INTEGER,
                shuffle BOOLEAN,
                FOREIGN KEY (creatorId) REFERENCES users(id)
            );
            SQL
        );
    }


    /*
     * Formats date using RFC 3339 format
     */
    public static function GetDateTimeFormat(?DateTime $dateTime): string
    {
        if ($dateTime === NULL) {
            return "";
        }

        /// RFC 3339 date time format
        return $dateTime->format(DATE_RFC3339);
    }

    /*
     * Reads date using RFC 3339 format
     */
    public static function GetDateTime(?string $formatted): ?DateTime
    {
        // If empty string is given
        if ($formatted == NULL) {
            return NULL;
        }

        try {
            // Try to parse date
            return new DateTime($formatted);
        } catch (Exception $e) {
            // Couldn't do this
            return NULL;
        }
    }

    public static function IsQuestionCorrect($question): bool
    {
        // Check for having at least 2 letters
        return preg_match("~[A-Za-zА-Яа-яЁё]{2,}~", $question)
            // Check for city phone number with an area code
            && !preg_match("~\( \d{3} \) (\d [-\s]?){5}~x", $question)
            // Check for city phone number without an area code
            && !preg_match("~(\d [-\s]?){6}~x", $question)
            // Check for mobile phone number with or without calling code
            && !preg_match("~( \+ \d{1,2} )( \d[-\s]? ){10,12}~x", $question);
    }

    private static function GenerateUniqueUrl(DbAccess $db, int $len): string
    {
        // Range of available characters
        $possibleChars = "abcdefghijklmnopqrstuvwxyz"
            . "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
            . "0123456789";
        $posLen = strlen($possibleChars);

        mt_srand(time());

        $stmt = $db->PrepareStatement(<<<SQL
            SELECT 1 FROM polls WHERE url = :url;
        SQL
        );
        $stmt->bindParam(':url', $url, PDO::PARAM_STR);

        do {
            // Generate random char from the given range and append it
            $url = "";
            for ($i = 0; $i < $len; $i++) {
                // If can use cryptographic generator
                try {
                    // Use it
                    $r = random_int(0, $posLen - 1);
                } catch (Exception $e) {
                    // Not cryptographic generator
                    $r = mt_rand(0, $posLen - 1);
                }

                $url .= $possibleChars[$r];
            }
        } // If exists url, try more
        while ($stmt->execute() && $stmt->fetch());

        return $url;
    }
}