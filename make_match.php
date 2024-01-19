<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "turnen";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function makeMatch($conn, $name, $gender) {
    $query = "INSERT INTO `wedstrijden`(`Naam`, `m/f`) VALUES ('" . $name . "','" . $gender . "')";
    $result = $conn->query($query);

    if ($result === FALSE) {
        die("Error in query: " . $conn->error);
    }

    $selectQuery = "SELECT * FROM `wedstrijden` ORDER BY `id` DESC LIMIT 1";
    $selectResult = $conn->query($selectQuery);

    if ($selectResult === FALSE) {
        die("Error in query: " . $conn->error);
    }

    $scores = array();

    while ($row = $selectResult->fetch_assoc()) {
        $scores[] = $row;
    }

    return $scores;
}

function makeMatchHasPlayers($conn, $wedstrijd_id, $deelnemers_id) {
    $query = "INSERT INTO `wedstrijden_has_deelnemers`(`wedstrijden_id`, `deelnemers_id`) VALUES ('" . $wedstrijd_id . "','" . $deelnemers_id . "')";
    $result = $conn->query($query);
}

function getAllUsers($conn, $geslacht = null) {
    if ($geslacht !== "f" && $geslacht !== "m") {
        $query = "SELECT * FROM deelnemers;";
    } else {
        $query = "SELECT * FROM deelnemers WHERE `geslacht` = '{$_GET['gender']}';";
    }

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

if (isset($_POST['create'])) {
    $selectedCheckboxes = isset($_POST['selectedCheckboxes']) ? json_decode($_POST['selectedCheckboxes'], true) : array();
    var_dump($selectedCheckboxes[0]);

    $wedstrijd_id = makeMatch($conn, $_POST['title'], $_POST['gender'])[0]['id'];

    foreach ($selectedCheckboxes as $boxes) {
        makeMatchHasPlayers($conn, $wedstrijd_id, $boxes);
    }
}

if (isset($_GET['gender'])) {
    $deelnemers = getAllUsers($conn, $_GET['gender']);
} else {
    $deelnemers = getAllUsers($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scores</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: white;
        }

        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #4caf50;
            color: white;
        }

        h1,
        h2 {
            text-align: center;
            color: #333333;
        }

        .score-container {
            display: block;
            background-color: #f4f4f4;
        }

        /* Style for the filter form */
        form[name="filter"] {
            margin-top: 20px;
            text-align: center;
        }

        /* Style for the filter select element */
        select[name="gender"] {
            padding: 8px;
            font-size: 16px;
            margin-right: 10px;
        }

        /* Style for the filter button */
        input[type="submit"][value="Filteren"] {
            padding: 8px 12px;
            font-size: 16px;
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
        }

        /* Style for the create match form */
        form[name="create"] {
            margin-top: 20px;
            text-align: center;
        }

        /* Style for the input elements in the create match form */
        input[type="text"],
        select[name="gender"],
        input[type="submit"][value="aanmaken"] {
            padding: 8px;
            font-size: 16px;
            margin-right: 10px;
        }

        /* Style for the "Alles" checkbox label */
        th input[type="checkbox"] {
            margin-right: 5px;
        }

        /* Style for the create match button */
        input[type="submit"][name="create"] {
            padding: 8px 12px;
            font-size: 16px;
            background-color: #4caf50;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <h1>Deelnemer(s)</h1>
    <div id="scoreboard-container" class="score-container">
        <h2>Algemeen Scorebord</h2>
        <form method="post" action="make_match.php" name="create" onsubmit="updateCheckboxValues()">
            <input placeholder="Naam van de wedstrijd" type="text" name="title">
            <select name="gender">
                <option value="null">Kies een geslacht</option>
                <option value="1">Man</option>
                <option value="2">Vrouw</option>
            </select>
            <input type="submit" value="aanmaken" name="create">
            <input type="hidden" name="selectedCheckboxes" id="selectedCheckboxes" value="">
        </form>
        <!-- <form method="get" action="make_match.php" name="filter">
            <select name="gender">
                <option value="null">Kies een geslacht</option>
                <option value="m">Man</option>
                <option value="f">Vrouw</option>
            </select>
            <input type="submit" value="Filteren">
        </form> -->
        <table>
            <tr>
                <th style="width: auto !important; display: flex !important;"><input type="checkbox" onclick="enableAllUsers()" />Alles</th>
                <th>Naam</th>
                <th>Geslacht</th>
            </tr>
            <?php foreach ($deelnemers as $score) : ?>
                <tr>
                    <td style="width: 20px !important;">
                        <input type="checkbox" class="checkBoxDeelnemers" name="checkboxes[]" value="<?php echo $score['id'] ?>" />
                    </td>
                    <td><?php echo $score['name']; ?></td>
                    <td><?php
                        if ($score['geslacht'] === "f") {
                            echo "Vrouw";
                        } else {
                            echo "Man";
                        }
                        ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>

</html>
<script>
    var count = 0;

    function enableAllUsers() {
        var checkboxes = document.querySelectorAll('.checkBoxDeelnemers');

        checkboxes.forEach(function (checkbox) {
            if (count % 2) {
                checkbox.checked = false;
                console.log('test');
            } else {
                checkbox.checked = true;
            }
        });

        count++;
    }

    function updateCheckboxValues() {
        var checkboxes = document.querySelectorAll('.checkBoxDeelnemers');
        var selectedValues = [];

        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                selectedValues.push(checkbox.value);
            }
        });

        document.getElementById('selectedCheckboxes').value = JSON.stringify(selectedValues);
    }
</script>
