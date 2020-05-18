<?php

require_once "classes/DbAccess.php";

$res = array();

if (isset($_POST["gender"])
    && isset($_POST["pollId"])
    && isset($_POST["yearFrom"])
    && isset($_POST["yearTo"])) {

    if (!isset($db)) {
        $db = new DbAccess();
    }

    $yearFrom = $_POST["yearFrom"] == -1 ? '-999' : $_POST["yearFrom"];
    $yearTo = $_POST["yearTo"] == -1 ? '9999' : $_POST["yearTo"];
    $gender = $_POST["gender"] === 'any'
        ? ""
        : "AND users.gender = {$_POST['gender']}";

    $pollId = $_POST['pollId'];

    $query = <<<SQL
	SELECT COUNT(q.variantId) as count
	FROM variants
    LEFT JOIN (
        SELECT votes.variantId
        FROM votes
        JOIN users ON votes.voterId = users.Id
        WHERE votes.pollId = {$pollId}
            AND users.year BETWEEN {$yearFrom} AND {$yearTo}
            {$gender}
    ) q ON q.variantId = variants.id
	WHERE variants.pollId = {$pollId}
    GROUP BY variants.id
    ORDER BY variants.id;
SQL;

    $votes = $db->SQLMultiple($query);

    $data = array();
    while ($vote = $votes->fetch(PDO::FETCH_ASSOC)) {
        $data[] = (int)$vote['count'];
    }

    $res["data"] = $data;

    $res['min'] = min($data);
    $res['max'] = max($data);

    // Find middle
    $a = $data;
    asort($data);
    $count = count($a);
    if ($count % 2 != 0) {
        $med = floor($count / 2);
    } else {
        $med = $count / 2;
        $two = $a[$med];
        $one = $a[$med - 1];
        $med = ($one + $two) / 2;
    }

    $res['med'] = $med;
    $res['avg'] = array_sum($data) / $count;

    $stdev = 0;
    foreach ($data as $i => $x) {
        $dif = $res['avg'] - $x;
        $stdev = $dif * $dif;
    }
    $stdev /= $count;
    $stdev = sqrt($stdev);
    $res['stdev'] = $stdev;

    $res['success'] = (int)($data != NULL);
} else {
    $res['success'] = 0;
}

echo json_encode($res);