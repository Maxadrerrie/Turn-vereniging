<?php

$csvFilePath = 'example.csv';

// Open the CSV file for reading
$file = fopen($csvFilePath, 'r');

// Check if the file was opened successfully
if ($file === false) {
    die('Failed to open the file.');
}

// Initialize an empty array to store the CSV data
$csvData = [];

// Loop through each row in the CSV file
while (($row = fgetcsv($file)) !== false) {
    // Add the row data to the array
    $csvData[] = $row;
}

// Close the file
fclose($file);

// Print the array for demonstration purposes
print_r($csvData);

$serverName = "localhost";
$dbUsername = "root";
$dbPassword = "";
$dbName = "turnen";

$conn = mysqli_connect($serverName, $dbUsername, $dbPassword, $dbName);

foreach ($csvData as $tussenRow) {
    foreach ($tussenRow as $row) {
        // Use prepared statements to avoid SQL injection
        $query = "SELECT * FROM deelnemers WHERE name = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $row); // "s" represents a string, adjust if necessary
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $query = "INSERT INTO deelnemers (name) VALUES (?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $row); // "s" represents a string, adjust if necessary
            $stmt->execute();
        }
        
        $stmt->close();
    }
}

// Close the database connection
mysqli_close($conn);
?>
