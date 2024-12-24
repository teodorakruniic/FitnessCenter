<?php
session_start();
require_once 'db_connection.php'; // tukaj je $pdo

// Preverimo, ali je uporabnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Preverimo, ali imamo ID (prisotnosti) v URL-ju
if (!isset($_GET['id'])) {
    // Če ni ID-ja, gremo nazaj na profil
    header('Location: profile.php');
    exit();
}

$attendanceId = (int)$_GET['id'];

try {
    // Brišemo samo, če je ID_uporabnika enak session user_id
    $stmt = $pdo->prepare("
        DELETE FROM prisotnosti
        WHERE ID_prisotnosti = :id
          AND ID_uporabnika = :userId
        LIMIT 1
    ");
    $stmt->bindValue(':id', $attendanceId, PDO::PARAM_INT);
    $stmt->bindValue(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    header('Location: profile.php');
    exit();

} catch (PDOException $e) {
    echo "Napaka pri brisanju: " . $e->getMessage();
}
