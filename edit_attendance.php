<?php
session_start();
require_once 'db_connection.php'; // tukaj je $pdo

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Obdelava obrazca, če je POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceId = (int)$_POST['attendanceId'];
    $prisotnost   = $_POST['prisotnost'];
    $datum        = $_POST['datum'];
    $cas          = $_POST['cas'];

    try {
        $sql = "
            UPDATE prisotnosti
            SET prisotnost = :prisotnost,
                datum = :datum,
                cas = :cas
            WHERE ID_prisotnosti = :id
              AND ID_uporabnika = :userId
            LIMIT 1
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':prisotnost', $prisotnost, PDO::PARAM_STR);
        $stmt->bindValue(':datum', $datum, PDO::PARAM_STR);
        $stmt->bindValue(':cas', $cas, PDO::PARAM_STR);
        $stmt->bindValue(':id', $attendanceId, PDO::PARAM_INT);
        $stmt->bindValue(':userId', $_SESSION['user_id'], PDO::PARAM_INT);

        $stmt->execute();

        // Preusmeritev nazaj na profil po uspešnem shranjevanju
        header('Location: profile.php');
        exit();
    } catch (PDOException $e) {
        echo "Napaka pri posodabljanju: " . $e->getMessage();
        exit();
    }
}

// Če ni POST, hočemo prikazati formo za urejanje.
if (!isset($_GET['id'])) {
    header('Location: profile.php');
    exit();
}

$attendanceId = (int)$_GET['id'];

// Pridobimo obstoječe podatke, da jih prikažemo v formi
try {
    $stmt = $pdo->prepare("
        SELECT ID_uporabnika, prisotnost, datum, cas
        FROM prisotnosti
        WHERE ID_prisotnosti = :id
        LIMIT 1
    ");
    $stmt->bindValue(':id', $attendanceId, PDO::PARAM_INT);
    $stmt->execute();
    $attendanceData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Preverimo, ali vnos obstaja in pripada temu uporabniku
    if (!$attendanceData || $attendanceData['ID_uporabnika'] != $_SESSION['user_id']) {
        header('Location: profile.php');
        exit();
    }
} catch (PDOException $e) {
    echo "Napaka pri branju iz baze: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Attendance</title>
  <!-- Če želiš, lahko vse stile daš v zunanjo .css datoteko -->
  <link rel="stylesheet" href="edit_attendance.css">
</head>
<body>

  <div class="wrapper">
    <h1>Edit Attendance</h1>
    <form method="POST" action="edit_attendance.php">
      
      <!-- Skrit input, ki hrani ID prisotnosti -->
      <input type="hidden" name="attendanceId" value="<?php echo $attendanceId; ?>">

      <!-- 1. Izbira prisotnosti (Present/Absent) -->
      <div class="input-box">
        <select name="prisotnost" required>
          <option value="present" <?php if ($attendanceData['prisotnost'] === 'present') echo 'selected'; ?>>
            Present
          </option>
          <option value="absent" <?php if ($attendanceData['prisotnost'] === 'absent') echo 'selected'; ?>>
            Absent
          </option>
        </select>
      </div>

      <!-- 2. Datum -->
      <div class="input-box">
        <input 
          type="date" 
          name="datum" 
          value="<?php echo htmlspecialchars($attendanceData['datum']); ?>" 
          required
        >
      </div>

      <!-- 3. Čas -->
      <div class="input-box">
        <input 
          type="time" 
          name="cas" 
          value="<?php echo htmlspecialchars($attendanceData['cas']); ?>" 
          required
        >
      </div>

      <!-- Gumb za potrditev -->
      <button type="submit" class="btn">Save Changes</button>
    </form>
  </div>

</body>
</html>
