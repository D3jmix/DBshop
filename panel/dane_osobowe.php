<?php
session_start();
require_once 'db.php';

$zalogowany = false;
$username = '';
$wiadomosc = '';

function zaladujDaneUzytkownika($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT k.username, k.email, c.imie, c.nazwisko, c.nr_tel, c.ulica, c.numer_domu, c.kod_pocztowy, c.miasto, c.kraj FROM konta k JOIN klienci c ON k.id_klienta = c.id_klienta WHERE k.id = :id");
    $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_SESSION['user_id'])) {
    $user = zaladujDaneUzytkownika($pdo, $_SESSION['user_id']);
    if ($user) {
        $zalogowany = true;
        $username = $user['username'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['zapisz'])) {
    if ($zalogowany) {
        $imie = $_POST['imie'];
        $nazwisko = $_POST['nazwisko'];
        $email = $_POST['email'];
        $telefon = $_POST['telefon'];
        $ulica = $_POST['ulica'];
        $numer_domu = $_POST['numer_domu'];
        $kod_pocztowy = $_POST['kod_pocztowy'];
        $miasto = $_POST['miasto'];
        $kraj = $_POST['kraj'];

        $bledy = array();

        if (preg_match('/\d/', $imie)) {
            $bledy[] = "Imię nie może zawierać cyfr.";
        }
        if (preg_match('/\d/', $nazwisko)) {
            $bledy[] = "Nazwisko nie może zawierać cyfr.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $bledy[] = "Niepoprawny format adresu email.";
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM konta WHERE email = :email AND id != :user_id");
            $stmt->execute([':email' => $email, ':user_id' => $_SESSION['user_id']]);
            if ($stmt->fetchColumn() > 0) {
                $bledy[] = "Podany adres email jest już zajęty.";
            }
        }

        if (!preg_match('/^\d{9}$|^\+\d{11}$/', $telefon)) {
            $bledy[] = "Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+').";
        }

        if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
            $bledy[] = "Kod pocztowy musi być w formacie XX-XXX (np. 01-234).";
        }

        if (count($bledy) > 0) {
            $wiadomosc = implode('<br>', $bledy);
        } else {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("UPDATE klienci SET imie = :imie, nazwisko = :nazwisko, nr_tel = :telefon, ulica = :ulica, numer_domu = :numer_domu, kod_pocztowy = :kod_pocztowy, miasto = :miasto, kraj = :kraj
                    WHERE id_klienta = (SELECT id_klienta FROM konta WHERE id = :user_id)");
                $stmt->execute([
                    ':imie' => $imie,
                    ':nazwisko' => $nazwisko,
                    ':telefon' => $telefon,
                    ':ulica' => $ulica,
                    ':numer_domu' => $numer_domu,
                    ':kod_pocztowy' => $kod_pocztowy,
                    ':miasto' => $miasto,
                    ':kraj' => $kraj,
                    ':user_id' => $_SESSION['user_id']
                ]);

                $stmt = $pdo->prepare("UPDATE konta SET email = :email WHERE id = :user_id");
                $stmt->execute([
                    ':email' => $email,
                    ':user_id' => $_SESSION['user_id']
                ]);

                $pdo->commit();
                $wiadomosc = "Dane zostały zaktualizowane pomyślnie.";

                $user = zaladujDaneUzytkownika($pdo, $_SESSION['user_id']);
            } catch (Exception $e) {
                $pdo->rollBack();
                $wiadomosc = "Wystąpił błąd podczas aktualizacji danych: " . $e->getMessage();
            }
        }
    }
}
?>


    <div class="container mt-4">
        <?php if ($wiadomosc): ?>
            <div class="alert alert-info"><?= htmlspecialchars($wiadomosc) ?></div>
        <?php endif; ?>

        <?php if ($zalogowany): ?>
            <h2>Witaj, <?= htmlspecialchars($username) ?>!</h2>
            <hr>
            <h3>Dane osobowe</h3>
            <form method="POST">
                <div class="mb-3">
                    <label for="imie" class="form-label">Imię</label>
                    <input type="text" name="imie" id="imie" class="form-control"
                           value="<?= htmlspecialchars($user['imie']) ?>" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
                </div>
                <div class="mb-3">
                    <label for="nazwisko" class="form-label">Nazwisko</label>
                    <input type="text" name="nazwisko" id="nazwisko" class="form-control"
                           value="<?= htmlspecialchars($user['nazwisko']) ?>" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Adres e-mail</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="telefon" class="form-label">Numer telefonu</label>
                    <input type="text" name="telefon" id="telefon" class="form-control"
                           value="<?= htmlspecialchars($user['nr_tel']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="ulica" class="form-label">Ulica</label>
                    <input type="text" name="ulica" id="ulica" class="form-control"
                           value="<?= htmlspecialchars($user['ulica']) ?>">
                </div>
                <div class="mb-3">
                    <label for="numer_domu" class="form-label">Numer domu</label>
                    <input type="text" name="numer_domu" id="numer_domu" class="form-control"
                           value="<?= htmlspecialchars($user['numer_domu']) ?>">
                </div>
                <div class="mb-3">
                    <label for="kod_pocztowy" class="form-label">Kod pocztowy</label>
                    <input type="text" name="kod_pocztowy" id="kod_pocztowy" class="form-control"
                           value="<?= htmlspecialchars($user['kod_pocztowy']) ?>">
                </div>
                <div class="mb-3">
                    <label for="miasto" class="form-label">Miasto</label>
                    <input type="text" name="miasto" id="miasto" class="form-control"
                           value="<?= htmlspecialchars($user['miasto']) ?>">
                </div>
                <div class="mb-3">
                    <label for="kraj" class="form-label">Kraj</label>
                    <input type="text" name="kraj" id="kraj" class="form-control"
                           value="<?= htmlspecialchars($user['kraj']) ?>">
                </div>
                <button type="submit" name="zapisz" class="btn btn-primary">Zapisz zmiany</button>
            </form>
        <?php else: ?>
            <p>Musisz się zalogować, aby uzyskać dostęp do panelu użytkownika.</p>
            <a href="login.php" class="btn btn-primary">Zaloguj się</a>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
