<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Points</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }

        form {
            width: 50%;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        label, input {
            display: block;
            margin-bottom: 10px;
        }

        input {
            width: calc(100% - 22px);
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4caf50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        h1, h2 {
            text-align: center;
            color: #333333;
        }

        .previous-score {
            text-align: center;
            color: #555555;
            margin-top: 20px;
        }

        .previous-score p {
            margin-bottom: 5px;
        }

        .update-score-form {
            width: 50%;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .score-doesnt {
            text-align: center;
            color: red;
            margin-top: 20px;
        }

        .score-exists {
            text-align: center;
            color: green;
            margin-top: 20px;
        }

        .participant-info {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "turnen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getPreviousScore($conn, $participantId) {
    $query = "SELECT d_points, e_points, penalty_points, total_points FROM points WHERE deelnemers_id = '$participantId' LIMIT 1";
    $result = $conn->query($query);

    if ($result === FALSE) {
        die("Error in query: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row;
    } else {
        return "Geen vorige score verkrijgbaar";
    }
}

$checkScoreQuery = "SELECT deelnemers_id FROM points WHERE deelnemers_id = '{$_GET['participant_id']}' LIMIT 1";
$checkScoreResult = $conn->query($checkScoreQuery);
$scoreExists = $checkScoreResult->num_rows > 0;
?>

<!-- Display participant info -->
<div class="participant-info">
    <?php
    // Fetch the person's name based on the user ID
    $participantId = $_GET['participant_id'];
    $nameQuery = "SELECT name FROM deelnemers WHERE id = '$participantId' LIMIT 1";
    $nameResult = $conn->query($nameQuery);

    if ($nameResult && $nameResult->num_rows > 0) {
        $row = $nameResult->fetch_assoc();
        $personName = $row['name'];
        echo "<h2>Geselecteerde Deelnemer: $personName</h2>";
    } else {
        echo "<h2>Geselecteerde Deelnemer ID: $participantId</h2>";
    }
    ?>
</div>

<!-- Assign Points Form -->
<form method="post" action="">
    <h1>Punten toekennen</h1>
    <?php if ($scoreExists) : ?>
        <div class="score-doesnt">
            <p>Er is al een score opgeslagen voor deze deelnemer.</p>
        </div>
    <?php else : ?>
        <div class="score-exists">
            <p>Er is nog geen score opgeslagen voor deze deelnemer.</p>
        </div>
    <?php endif; ?>
    <input type="hidden" name="participant_id" value="<?php echo $_GET['participant_id']; ?>">
    <label for="d_points">D Punten:</label>
    <input type="text" name="d_points" <?php echo $scoreExists ? 'disabled' : 'required'; ?>>
    <label for="e_points">E Punten:</label>
    <input type="text" name="e_points" <?php echo $scoreExists ? 'disabled' : 'required'; ?>>
    <label for="penalty_points">Penalty Punten:</label>
    <input type="text" name="penalty_points" <?php echo $scoreExists ? 'disabled' : 'required'; ?>>
    <button type="submit" <?php echo $scoreExists ? 'disabled' : ''; ?>>Assign Punten</button>
</form>

<!-- Update Score Form -->
<form method="post" action="">
    <h1>Update vorige score</h1>
    <input type="hidden" name="participant_id" value="<?php echo $_GET['participant_id']; ?>">
    <label for="updated_d_points">Updated D Punten:</label>
    <input type="text" name="updated_d_points" required>
    <label for="updated_e_points">Updated E Punten:</label>
    <input type="text" name="updated_e_points" required>
    <label for="updated_penalty_points">Updated Penalty Punten:</label>
    <input type="text" name="updated_penalty_points" required>
    <button type="submit" name="update_score">Update Score</button>
</form>

<!-- Display Previous Score -->
<div class="previous-score">
    <h1>Vorige score</h1>
    <?php
    $previousScore = getPreviousScore($conn, $_GET['participant_id']);
    if (is_array($previousScore)) {
        echo "<p>D Punten: {$previousScore['d_points']}</p>";
        echo "<p>E Punten: {$previousScore['e_points']}</p>";
        echo "<p>Penalty Punten: {$previousScore['penalty_points']}</p>";
        echo "<p>Totaal Aantal Punten: {$previousScore['total_points']}</p>";
    } else {
        echo "<p>{$previousScore}</p>";
    }
    ?>
</div>

</body>
</html>
