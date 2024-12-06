<?php
session_start();
require_once 'db.php';

$zalogowany = false;
$username = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username FROM konta WHERE id=:id");
    $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $zalogowany = true;
        $username = $user['username'];
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<div class="container mt-4">
    <h2>Pomoc</h2>
    <p>Witaj na stronie pomocy! Jeśli potrzebujesz wsparcia, zapoznaj się z poniższymi sekcjami.</p>

    <h4>Najczęściej zadawane pytania (FAQ)</h4>
    <p>Jeśli masz pytania dotyczące naszych usług, sprawdź naszą stronę z najczęściej zadawanymi pytaniami (FAQ).</p>
    <a href="faq.php" class="btn btn-primary">Przejdź do FAQ</a>

    <h4>Kontakt z nami</h4>
    <p>Masz pytanie lub problem? Skontaktuj się z nami, klikając poniższy link.</p>
    <a href="kontakt.php" class="btn btn-primary">Skontaktuj się z nami</a>

    <h4>Formularz zwrotu</h4>
    <p>Chciałbyś zwrócić produkt? Skorzystaj z formularza zwrotów.</p>
    <a href="formularz_reklamacji.php" class="btn btn-primary">Formularz reklamacji</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
