<?php
ob_start();
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'];

    $query = "SELECT password, id_klienta FROM konta WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    if (password_verify($password, $row['password'])) {
    $id_klienta = $row['id_klienta'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM konta WHERE id = ?");
        $stmt->execute([$user_id]);

        $stmt = $pdo->prepare("DELETE FROM klienci WHERE id_klienta = ?");
        $stmt->execute([$id_klienta]);

        $pdo->commit();

        session_destroy();

        // Przekierowanie do zegnaj.php po usunięciu konta
        header('Location: zegnaj.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Wystąpił błąd: " . $e->getMessage();
    }
}
}
?>

<body>
<div class="container mt-4">
    <h2>Zarządzanie kontem</h2>
    <p>Aby dezaktywować konto, wprowadź swoje hasło poniżej.</p>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?= $error_message ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="password" class="form-label">Hasło</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger">Usuń konto</button>
    </form>

    
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>




