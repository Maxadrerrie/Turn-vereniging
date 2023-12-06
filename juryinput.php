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

// Fetch divisions for filter
$divisionsResult = $conn->query("SELECT * FROM divisies");
?>

<form method="post" action="">
    <label for="division">Select Division:</label>
    <select name="division" id="division">
        <option value="">All Divisions</option>
        <?php
        while ($division = $divisionsResult->fetch_assoc()) {
            echo "<option value='" . $division['id'] . "'>" . $division['naam'] . "</option>";
        }
        ?>
    </select>
    <button type="submit">Filter</button>
</form>

<table>
    <tr>
        <th>Name</th>
        <th>Division</th>
        <th>Action</th>
    </tr>

    <?php
    // Handle the form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selectedDivision = $_POST['division'];
        $sql = "SELECT * FROM deelnemers WHERE '$selectedDivision' = '' OR '$selectedDivision' = divisies_id";
    } else {
        $sql = "SELECT * FROM deelnemers";
    }

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['divisies_id'] . "</td>";
        echo "<td><a href='assign_points.php?participant_id=" . $row['id'] . "'>Assign Points</a></td>";
        echo "</tr>";
    }

    // Close the database connection
    $conn->close();
    ?>
</table>

</body>
</html>
