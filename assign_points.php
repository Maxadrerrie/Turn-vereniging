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
    </style>
</head>
<body>

<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "turnen";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
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
        return "No previous score available";
    }
}

// Handle form submission for updating previous score
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_score'])) {
    $participantId = $_POST['participant_id'];
    $updatedDPoints = $_POST['updated_d_points'];
    $updatedEPoints = $_POST['updated_e_points'];
    $updatedPenaltyPoints = $_POST['updated_penalty_points'];

    $updatedTotalPoints = $updatedDPoints + $updatedEPoints - $updatedPenaltyPoints;

    // Update the previous score in the database
    $updateQuery = "UPDATE points SET d_points = '$updatedDPoints', e_points = '$updatedEPoints', penalty_points = '$updatedPenaltyPoints', total_points = '$updatedTotalPoints' WHERE deelnemers_id = '$participantId'";
    if ($conn->query($updateQuery) === TRUE) {
        echo "Previous score updated successfully.";
    } else {
        echo "Error updating previous score: " . $conn->error;
    }
}

$previousScore = getPreviousScore($conn, $_GET['participant_id']);
?>

<!-- Assign Points Form -->
<form method="post" action="">
    <h1>Punten toekennen</h1>
    <input type="hidden" name="participant_id" value="<?php echo $_GET['participant_id']; ?>">
    <label for="d_points">D Points:</label>
    <input type="text" name="d_points" required>
    <label for="e_points">E Points:</label>
    <input type="text" name="e_points" required>
    <label for="penalty_points">Penalty Points:</label>
    <input type="text" name="penalty_points" required>
    <button type="submit">Assign Points</button>
</form>

<!-- Update Score Form -->
    <form method="post" action="">
    <h1>Update vorige score</h1>
        <input type="hidden" name="participant_id" value="<?php echo $_GET['participant_id']; ?>">
        <label for="updated_d_points">Updated D Points:</label>
        <input type="text" name="updated_d_points" required>
        <label for="updated_e_points">Updated E Points:</label>
        <input type="text" name="updated_e_points" required>
        <label for="updated_penalty_points">Updated Penalty Points:</label>
        <input type="text" name="updated_penalty_points" required>
        <button type="submit" name="update_score">Update Score</button>
    </form>
</div>

<!-- Display Previous Score -->
<div class="previous-score">
    <h1>Vorige score</h1>
    <?php
    if (is_array($previousScore)) {
        echo "<p>D Points: {$previousScore['d_points']}</p>";
        echo "<p>E Points: {$previousScore['e_points']}</p>";
        echo "<p>Penalty Points: {$previousScore['penalty_points']}</p>";
        echo "<p>Total Points: {$previousScore['total_points']}</p>";
    } else {
        echo "<p>{$previousScore}</p>";
    }
    ?>
</div>

</body>
</html>
