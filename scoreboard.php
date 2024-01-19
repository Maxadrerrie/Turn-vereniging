<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "turnen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getAllScores($conn) {
    // Query voor recente score
    $queryRecent = "SELECT deelnemers.name, 
                          points.d_points,
                          points.e_points,
                          points.penalty_points,
                          points.d_points + points.e_points - points.penalty_points as total_points
                   FROM deelnemers
                   JOIN points ON deelnemers.id = points.deelnemers_id
                   ORDER BY points.oefening_id DESC  -- Vervang 'oefening_id' met de juiste kolomnaam
                   LIMIT 1";

    $resultRecent = $conn->query($queryRecent);

    if ($resultRecent === FALSE) {
        die("Error in query: " . $conn->error);
    }

    $recentScore = $resultRecent->fetch_assoc();

    // Query voor totale scores per deelnemer
    $queryAll = "SELECT deelnemers.id, deelnemers.name, 
                         SUM(points.d_points) as total_d_points,
                         SUM(points.e_points) as total_e_points,
                         SUM(points.penalty_points) as total_penalty_points,
                         SUM(points.d_points + points.e_points - points.penalty_points) as total_points
                  FROM deelnemers
                  JOIN points ON deelnemers.id = points.deelnemers_id
                  GROUP BY deelnemers.id
                  ORDER BY total_points DESC";

    $resultAll = $conn->query($queryAll);

    if ($resultAll === FALSE) {
        die("Error in query: " . $conn->error);
    }

    $scores = array();
    while ($row = $resultAll->fetch_assoc()) {
        $scores[] = $row;
    }

    $scores['recent'] = $recentScore;

    return $scores;
}

$scores = getAllScores($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">

    <title>Scores</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #4caf50;
            color: white;
        }

        h1, h2 {
            text-align: center;
            color: #333333;
        }

        .score-container {
            display: none;
        }
    </style>
</head>
<body>

<h1>Scores</h1>

<!-- Meest recente score scherm -->
<div id="recent-score-container" class="score-container">
    <h2>Meest recente score</h2>
    <?php if (!empty($scores['recent'])) : ?>
        <table>
            <tr>
                <th>Naam</th>
                <th>D Punten</th>
                <th>E Punten</th>
                <th>Penalty Punten</th>
                <th>Totaal Aantal Punten</th>
            </tr>
            <?php
            $recentScore = $scores['recent'];
            echo "<tr>";
            echo "<td>{$recentScore['name']}</td>";
            echo "<td>{$recentScore['d_points']}</td>";
            echo "<td>{$recentScore['e_points']}</td>";
            echo "<td>{$recentScore['penalty_points']}</td>";
            echo "<td>{$recentScore['total_points']}</td>";
            echo "</tr>";
            ?>
        </table>
    <?php else : ?>
        <p>Geen scores beschikbaar.</p>
    <?php endif; ?>
</div>

<!-- Algemeen scorebord scherm -->
<div id="scoreboard-container" class="score-container">
    <h2>Algemeen Scorebord</h2>
    <table>
        <tr>
            <th>Naam</th>
            <th>D Punten</th>
            <th>E Punten</th>
            <th>Penalty Punten</th>
            <th>Totaal Aantal Punten</th>
        </tr>
        <?php
        $uniqueIds = array();
        foreach ($scores as $score) :
            if (is_array($score) && array_key_exists('id', $score) && !in_array($score['id'], $uniqueIds)) :
                $uniqueIds[] = $score['id'];
        ?>
                <tr>
                    <td><?php echo $score['name']; ?></td>
                    <td><?php echo isset($score['total_d_points']) ? $score['total_d_points'] : ''; ?></td>
                    <td><?php echo isset($score['total_e_points']) ? $score['total_e_points'] : ''; ?></td>
                    <td><?php echo isset($score['total_penalty_points']) ? $score['total_penalty_points'] : ''; ?></td>
                    <td><?php echo isset($score['total_points']) ? $score['total_points'] : ''; ?></td>
                </tr>
        <?php
            endif;
        endforeach;
        ?>
    </table>
</div>

<script>
    // Toon eerst de algemene score en wissel dan naar de meest recente score na 3 seconden
    document.getElementById('recent-score-container').style.display = 'none';
    document.getElementById('scoreboard-container').style.display = 'block';

    setTimeout(function () {
        document.getElementById('recent-score-container').style.display = 'block';
        document.getElementById('scoreboard-container').style.display = 'none';
    }, 3000);

    // Wissel elke 6 seconden (3 seconden voor elk scherm)
    setInterval(function () {
        document.getElementById('recent-score-container').style.display =
            document.getElementById('recent-score-container').style.display === 'none' ? 'block' : 'none';
        document.getElementById('scoreboard-container').style.display =
            document.getElementById('scoreboard-container').style.display === 'none' ? 'block' : 'none';
    }, 3000);
</script>

</body>
</html>
