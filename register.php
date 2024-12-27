<?php
require_once 'db_connection.php'; // Uključi PDO konekciju

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validacija e-pošte
    if (!preg_match("/^[\w.-]+@[\w.-]+\.[A-Za-z]{2,}$/", $email)) {
        echo "Invalid email format. Please enter a valid email.";
        exit();
    }

    // Validacija gesla: vsaj 8 znakov, 1 velika črka, 1 mala črka, 1 številka
    if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/", $password)) {
        echo "Password must be at least 8 characters long, contain at least one uppercase letter, one lowercase letter, and one number.";
        exit();
    }

    // Geslo zgoščeno (hashed)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Pripremi SQL upit za unos korisnika
        $sql = "INSERT INTO uporabniki (ime, epošta, geslo) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($sql);

        // Poveži parametre
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);

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
