<?php
session_start();
require_once 'db_connection.php'; // tukaj je $pdo

// 1. Preverimo, ali je uporabnik prijavljen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

try {
    // 2. Poberi podatke za trenutno prijavljenega uporabnika
    $stmt = $pdo->prepare("
        SELECT u.ID_uporabnika, u.ime, u.epošta
        FROM uporabniki u
        WHERE u.ID_uporabnika = :userId
        LIMIT 1
    ");
    $stmt->bindParam(':userId', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Napaka: Uporabnik ni najden.";
        exit();
    }

    // 3. Poberi ZGODOVINO prisotnosti
    //    Tokrat vključimo tudi primarni ključ ID_prisotnosti
    $historyStmt = $pdo->prepare("
        SELECT ID_prisotnosti, prisotnost, datum, cas
        FROM prisotnosti
        WHERE ID_uporabnika = :userId
        ORDER BY datum DESC, cas DESC
    ");
    $historyStmt->bindParam(':userId', $user['ID_uporabnika'], PDO::PARAM_INT);
    $historyStmt->execute();
    $attendanceHistory = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Napaka pri povezavi z bazo: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Profile</title>
  <link rel="stylesheet" href="profile.css">
</head>
<body>
  <div class="wrapper">
    <h1>User Profile</h1>
    <div class="profile-box">
      <div class="user-image">
        <img src="slika3.jpg" alt="User Image">
      </div>
      <h2>Welcome, <?php echo htmlspecialchars($user['ime']); ?></h2>
      <p><strong>Email:</strong> <?php echo htmlspecialchars($user['epošta']); ?></p>
      <p><strong>Attendance History:</strong></p>

      <ul class="attendance-history">
        <?php if ($attendanceHistory): ?>
          <?php foreach ($attendanceHistory as $entry): ?>
            <li>
              <?php
                  // Izpišemo 'Present' ali 'Absent'
                  echo ($entry['prisotnost'] === 'present') ? 'Present' : 'Absent';
              ?>
              - <?php echo htmlspecialchars($entry['datum']); ?>
              <?php echo $entry['cas'] ? ' - ' . htmlspecialchars($entry['cas']) : ''; ?>

              <!-- Dodamo gumba "Edit" in "Delete" -->
              <a href="edit_attendance.php?id=<?php echo $entry['ID_prisotnosti']; ?>" style="margin-left: 10px;">
                Edit
              </a>
              <a
                href="delete_attendance.php?id=<?php echo $entry['ID_prisotnosti']; ?>"
                style="color: red; margin-left: 10px;"
                onclick="return confirm('Are you sure you want to delete this attendance record?');"
              >
                Delete
              </a>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li>No attendance history available.</li>
        <?php endif; ?>
      </ul>
    </div>
    <button class="btn-logout" onclick="logout()">Logout</button>

  </div>

  <script>
    function logout() {
      window.location.href = "logout.php";
    }
  </script>
</body>
</html>
