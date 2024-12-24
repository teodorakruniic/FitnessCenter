<?php
include 'db_connection.php';

$sql = "SELECT * FROM uporabniki";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['ID_uporabnika'] . " - Name: " . $row['ime'] . " " . $row['priimek'] . "<br>";
    }
} else {
    echo "No users found.";
}

$conn->close();
?>