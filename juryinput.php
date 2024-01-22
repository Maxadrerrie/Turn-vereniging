<?php

include 'header.html';
include 'connect.php';

$competitionsResult = $conn->query("SELECT * FROM wedstrijden");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jury Page</title>
    <link rel="stylesheet" href="css/juryinput.css">
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
