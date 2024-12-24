<?php
require_once 'db_connection.php'; // Uključi PDO konekciju

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Pripremi SQL upit za unos korisnika
        $sql = "INSERT INTO uporabniki (ime, epošta, geslo) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($sql);

        // Poveži parametre
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);

        // Izvrši upit
        if ($stmt->execute()) {
           header("Location: login.html");
        } else {
            echo "Error during registration.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
