<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "turnen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$competitionsResult = $conn->query("SELECT * FROM wedstrijden");
?>

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

        #filter-wedstrijd {
            width: 25%;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
            padding: 20px;
        }

        /* /* Apply styles to the filter box container / */
        .filter-box {
        width: 300px;
        margin: 20px;
        }

/* / Style the input field / */
        .filter-input {
        width: 100%;
        padding: 10px;
        margin-bottom: 10px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 5px;
        outline: none;
        }

/* / Style the filter button / */
        .filter-button {
        width: 100%;
        padding: 10px;
        background-color: #4caf50;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        outline: none;
        }

/* / Add hover effect for the button / */
        .filter-button:hover {
        background-color: #45a049;
        }

    </style>
</head>
<body>

<div id="filter-wedstrijd">
<form method="post" action="">
    <label for="competition">Selecteer wedstrijd:</label>
    <select class="filter-input" name="competition" id="competition">
        <option value="">Alle Wedstrijden</option>
        <?php while ($competition = $competitionsResult->fetch_assoc()) : ?>
            <option value="<?= $competition['id'] ?>"><?= $competition['Naam'] ?></option>
        <?php endwhile; ?>
    </select>

    <button class="filter-button" type="submit" name="filter">Filter</button>
</form>
</div>

<table>
    <tr>
        <th>Naam</th>
        <th>Geslacht</th>
        <th>Acties</th>
    </tr>

    <?php
    // Handle the form submission
    if (isset($_POST['filter'])) {
        $selectedCompetition = $_POST['competition'];
        $sql = "SELECT deelnemers.id, deelnemers.name, deelnemers.geslacht
        FROM deelnemers
        JOIN wedstrijden_has_deelnemers ON deelnemers.id = wedstrijden_has_deelnemers.deelnemers_id
        WHERE ('$selectedCompetition' = '' OR '$selectedCompetition' = wedstrijden_has_deelnemers.wedstrijden_id)";

    } else {
        $sql = "SELECT * FROM deelnemers";
    }

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['geslacht'] . "</td>";
        echo "<td><a href='assign_points.php?participant_id=" . $row['id'] . "'>Punten Toekennen</a></td>";
        echo "</tr>";
    }

    // Close the database connection
    $conn->close();
    ?>
</table>

</body>
</html>
