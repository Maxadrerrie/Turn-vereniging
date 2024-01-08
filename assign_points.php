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
    $query = "SELECT d_points, e_points, penalty_points FROM points WHERE deelnemers_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $participantId);
    $stmt->execute();
    $result = $stmt->get_result();

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

$participantId = $_GET['participant_id'];

// Process Update Score Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_score'])) {
    $oefeningId = $_POST['update_oefening_id'];
    $updatedDPoints = $_POST['updated_d_points'];
    $updatedEPoints = $_POST['updated_e_points'];
    $updatedPenaltyPoints = $_POST['updated_penalty_points'];
    $participantId = $_POST['participant_id'];

    // Check if a score exists for the participant
    $checkScoreQuery = "SELECT deelnemers_id FROM points WHERE deelnemers_id = ? AND oefening_id = ? LIMIT 1";
    $checkScoreResult = $conn->prepare($checkScoreQuery);
    $checkScoreResult->bind_param("ss", $participantId, $oefeningId);
    $checkScoreResult->execute();
    $scoreExists = $checkScoreResult->get_result()->num_rows > 0;

    if (!$scoreExists) {
        // Insert a new score if it doesn't exist
        $insertQuery = "INSERT INTO points (deelnemers_id, d_points, e_points, penalty_points, oefening_id)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sssss", $participantId, $updatedDPoints, $updatedEPoints, $updatedPenaltyPoints, $oefeningId);
        $stmt->execute();
    } else {
        // Update the existing score
        $updateQuery = "UPDATE points
                        SET d_points = ?,
                            e_points = ?,
                            penalty_points = ?
                        WHERE deelnemers_id = ? AND oefening_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sssss", $updatedDPoints, $updatedEPoints, $updatedPenaltyPoints, $participantId, $oefeningId);
        $stmt->execute();
    }
}
// Fetch the maximum participant_id from the database
$maxParticipantIdQuery = "SELECT MAX(id) AS max_id FROM deelnemers";
$maxParticipantIdResult = $conn->query($maxParticipantIdQuery);

if ($maxParticipantIdResult && $maxParticipantIdResult->num_rows > 0) {
    $maxParticipantIdRow = $maxParticipantIdResult->fetch_assoc();
    $maxParticipantId = $maxParticipantIdRow['max_id'];
} else {
    // Default to a value if no records are found
    $maxParticipantId = 1;
}
?>
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

        label, input, select {
            display: block;
            margin-bottom: 10px;
        }

        input, select {
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
<script>
    var maxParticipantId = <?php echo json_encode($maxParticipantId); ?>;
    
    function updateNextParticipantId() {
        // Get the current participant_id from the URL
        var currentParticipantId = <?php echo json_encode($_GET['participant_id']); ?>;
        
        // Increment the participant_id
        var nextParticipantId = parseInt(currentParticipantId) + 1;

        // If the next ID exceeds the maximum, loop back to the beginning
        if (nextParticipantId > maxParticipantId) {
            window.location.href = 'http://localhost/Turn-vereniging/juryinput.php';
        } else {
            // Update the form action with the new participant_id
            document.querySelector('form').action = '?participant_id=' + nextParticipantId;

            // Update the hidden input field with the new participant_id
            document.querySelector('input[name="participant_id"]').value = nextParticipantId;
            
            return true; // Allow the form submission to proceed
        }
    }
</script>

<!-- Display participant info -->
<div class="participant-info">
    <?php
    // Fetch the person's name based on the user ID
    $participantId = $_GET['participant_id'];
    $nameQuery = "SELECT name FROM deelnemers WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($nameQuery);
    $stmt->bind_param("s", $participantId);
    $stmt->execute();
    $nameResult = $stmt->get_result();

    if ($nameResult && $nameResult->num_rows > 0) {
        $row = $nameResult->fetch_assoc();
        $personName = $row['name'];
        echo "<h2>Geselecteerde Deelnemer: $personName</h2>";
    } else {
        echo "<h2>Geselecteerde Deelnemer ID: $participantId</h2>";
    }
    ?>
</div>

<!-- Update Score Form -->
<form method="post" action="" onsubmit="return updateNextParticipantId()">
    <h1>Update vorige score</h1>
    <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($_GET['participant_id']); ?>">
    <label for="update_oefening_id">Selecteer Oefening:</label>
    <select name="update_oefening_id">
        <?php
        // Fetch oefeningen from the database
        $oefeningQuery = "SELECT id, name FROM oefening";
        $oefeningResult = $conn->query($oefeningQuery);

        if ($oefeningResult && $oefeningResult->num_rows > 0) {
            while ($oefeningRow = $oefeningResult->fetch_assoc()) {
                echo "<option value='{$oefeningRow['id']}'>{$oefeningRow['name']}</option>";
            }
        }
        ?>
    </select>
    <label for="updated_d_points">Updated D Punten:</label>
    <input type="text" name="updated_d_points" required>
    <label for="updated_e_points">Updated E Punten:</label>
    <input type="text" name="updated_e_points" required>
    <label for="updated_penalty_points">Updated Penalty Punten:</label>
    <input type="text" name="updated_penalty_points" required>
    <input type="hidden" name="participant_id" value="<?php echo htmlspecialchars($_GET['participant_id']); ?>">
    <button type="submit" name="update_score">Update Score</button>
</form>

<!-- Display Previous Score -->
<div class="previous-score">
    <h1>Vorige score</h1>
    <?php
    $previousScore = getPreviousScore($conn, $_GET['participant_id']);
    if (is_array($previousScore)) {
        echo "<p>D Punten: " . htmlspecialchars($previousScore['d_points']) . "</p>";
        echo "<p>E Punten: " . htmlspecialchars($previousScore['e_points']) . "</p>";
        echo "<p>Penalty Punten: " . htmlspecialchars($previousScore['penalty_points']) . "</p>";
        echo "<p>Totaal Aantal Punten: " . ($previousScore['d_points'] + $previousScore['e_points'] - $previousScore['penalty_points']) . "</p>";
    } else {
        echo "<p>" . htmlspecialchars($previousScore) . "</p>";
    }
    ?>
</div>

</body>
</html>