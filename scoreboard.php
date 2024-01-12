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
    $query = "SELECT deelnemers.name, points.d_points, points.e_points, points.penalty_points, 
                     (points.d_points + points.e_points - points.penalty_points) as total_points
              FROM deelnemers
              JOIN points ON deelnemers.id = points.deelnemers_id
              ORDER BY total_points DESC";

    $result = $conn->query($query);

    if ($result === FALSE) {
        die("Error in query: " . $conn->error);
    }

    $scores = array();
    while ($row = $result->fetch_assoc()) {
        $scores[] = $row;
    }

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

    <!-- <meta http-equiv="refresh" content="3"> -->
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
    <?php if (!empty($scores)) : ?>
        <table>
            <tr>
                <th>Naam</th>
                <th>D Punten</th>
                <th>E Punten</th>
                <th>Penalty Punten</th>
                <th>Totaal Aantal Punten</th>
            </tr>
            <?php
            $recentScore = $scores[0];
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
        <?php foreach ($scores as $score) : ?>
            <tr>
                <td><?php echo $score['name']; ?></td>
                <td><?php echo $score['d_points']; ?></td>
                <td><?php echo $score['e_points']; ?></td>
                <td><?php echo $score['penalty_points']; ?></td>
                <td><?php echo $score['total_points']; ?></td>
            </tr>
        <?php endforeach; ?>
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
