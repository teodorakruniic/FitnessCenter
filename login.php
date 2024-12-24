<?php
session_start();
include 'db_connection.php'; // Uključuje fajl sa PDO konekcijom (promenljiva $pdo)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Preuzimanje kredencijala iz forme
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL upit - pronalazak korisnika sa unetim emailom
    $sql = "SELECT ID_uporabnika, geslo FROM uporabniki WHERE epošta = :email";
    $stmt = $pdo->prepare($sql);

    // Izvršavamo pripremljeni upit i prosleđujemo email
    $stmt->execute([':email' => $email]);

    // Preuzimamo rezultat iz baze
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Ako postoji korisnik sa tim emailom, proveravamo lozinku
        // Pretpostavka je da je geslo već heširano u bazi pa koristimo password_verify
        if (password_verify($password, $row['geslo'])) {
            // Ako je lozinka ispravna, čuvamo ID u sesiji
            $_SESSION['user_id'] = $row['ID_uporabnika'];

            // Preusmeravanje na željenu stranicu (prisotnost.html ili prisotnost.php)
            header("Location: prisotnost.html");
            exit();
        } else {
            // Neuspešna prijava - pogrešna lozinka
            echo "Invalid password.";
            exit();
        }
    } else {
        // Ne postoji nalog sa unetim emailom
        echo "No account found with that email.";
        exit();
    }
}
?>
