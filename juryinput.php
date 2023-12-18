<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jury Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        form {
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

$divisionsResult = $conn->query("SELECT * FROM divisies");
?>

<form method="post" action="">
    <label for="division">Selecteer divisies:</label>
    <select name="division" id="division">
        <option value="">Alle Divisies</option>
        <?php
        while ($division = $divisionsResult->fetch_assoc()) {
            echo "<option value='" . $division['id'] . "'>" . $division['naam'] . "</option>";
        }
        ?>
    </select>

    <label for="competition">Selecteer wedstrijd:</label>
    <select name="competition" id="competition">
        <option value="">Alle Wedstrijden</option>
        <?php
        $competitionsResult = $conn->query("SELECT * FROM wedstrijden");
        while ($competition = $competitionsResult->fetch_assoc()) {
            echo "<option value='" . $competition['id'] . "'>" . $competition['Naam'] . "</option>";
        }
        ?>
    </select>

    <button type="submit" name="filter">Filter</button>
</form>

<table>
    <tr>
        <th>Naam</th>
        <th>Divisie</th>
        <th>Acties</th>
    </tr>

    <?php
    // Handle the form submission
    if (isset($_POST['filter'])) {
        $selectedDivision = $_POST['division'];
        $selectedCompetition = $_POST['competition'];
        $sql = "SELECT deelnemers.name, deelnemers.divisies_id
                FROM deelnemers
                JOIN wedstrijden_has_deelnemers ON deelnemers.id = wedstrijden_has_deelnemers.deelnemers_id
                WHERE ('$selectedDivision' = '' OR '$selectedDivision' = deelnemers.divisies_id)
                    AND ('$selectedCompetition' = '' OR '$selectedCompetition' = wedstrijden_has_deelnemers.wedstrijden_id)";
    } else {
        $sql = "SELECT * FROM deelnemers";
    }

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['divisies_id'] . "</td>";
        echo "<td><a href='assign_points.php?participant_id=" . $row['id'] . "'>Punten Toekennen</a></td>";
        echo "</tr>";
    }

    // Close the database connection
    $conn->close();
    ?>
</table>

</body>
</html>
