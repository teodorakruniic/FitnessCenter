<?php
session_start();
require_once 'db_connection.php';
require_once __DIR__ . '/fpdf/fpdf186/fpdf.php';


// Proveri da li je korisnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // Preuzmi podatke korisnika
    $stmt = $pdo->prepare("SELECT ime FROM uporabniki WHERE ID_uporabnika = :userId LIMIT 1");
    $stmt->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Korisnik nije pronađen.");
    }

    // Preuzmi prisutnosti korisnika
    $historyStmt = $pdo->prepare("
        SELECT prisotnost, datum, cas
        FROM prisotnosti
        WHERE ID_uporabnika = :userId
        ORDER BY datum DESC, cas DESC
    ");
    $historyStmt->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $historyStmt->execute();
    $attendanceHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    // Generiši PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Attendance Report for ' . htmlspecialchars($user['ime']), 0, 1, 'C');
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($attendanceHistory as $entry) {
        $status = ($entry['prisotnost'] === 'present') ? 'Present' : 'Absent';
        $pdf->Cell(0, 10, $entry['datum'] . ' ' . $entry['cas'] . ' - ' . $status, 0, 1);
    }

    // Sačuvaj i pošalji PDF korisniku
    $pdf->Output('I', 'attendance_report.pdf');
} catch (PDOException $e) {
    echo "Greška: " . $e->getMessage();
    exit();
}
?>
