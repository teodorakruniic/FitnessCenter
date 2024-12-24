<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $attendance = $_POST['attendance'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Pronađi ID_uporabnika
    $stmt = $pdo->prepare("SELECT ID_uporabnika FROM uporabniki WHERE ime = :name LIMIT 1");
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = $user['ID_uporabnika'];

        // Unesi podatke u tabelu prisotnosti
        $stmt = $pdo->prepare("INSERT INTO prisotnosti (ID_uporabnika, prisotnost, datum, cas) 
                               VALUES (:userId, :attendance, :date, :time)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':attendance', $attendance, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);

        if ($stmt->execute()) {
            header("Location: profile.php"); 
        } else {
            echo "Greška pri unosu podataka.";
        }
    } else {
        echo "Korisnik nije pronađen.";
    }
}
?>
