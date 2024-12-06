<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_konto'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $nr_tel = $_POST['nr_tel'];
    $ulica = $_POST['ulica'] ?? null;
    $numer_domu = $_POST['numer_domu'] ?? null;
    $miasto = $_POST['miasto'] ?? null;
    $kod_pocztowy = $_POST['kod_pocztowy'] ?? null;
    $kraj = $_POST['kraj'] ?? null;
    $admin = isset($_POST['admin']) ? 1 : 0;
    $password_error = null;
    if (strlen($password) < 8 ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/\d/', $password) ||
        !preg_match('/[\W_]/', $password)) {
        $password_error = "Hasło musi mieć co najmniej 8 znaków, zawierać małą i dużą literę, cyfrę oraz znak specjalny.";
    }
    $phone_error = null;
    if (!preg_match('/^\d{9}$|^\+\d{11}$/', $nr_tel)) {
        $phone_error = "Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+')).";
    }
    $postal_code_error = null;
    if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
        $postal_code_error = "Kod pocztowy musi mieć format: 12-345.";
    }
    if ($password_error || $phone_error || $postal_code_error) {
        echo "<div class='alert alert-danger'>";
        if ($password_error) {
            echo "<p>" . htmlspecialchars($password_error) . "</p>";
        }
        if ($phone_error) {
            echo "<p>" . htmlspecialchars($phone_error) . "</p>";
        }
        if ($postal_code_error) {
            echo "<p>" . htmlspecialchars($postal_code_error) . "</p>";
        }
        echo "</div>";
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT);
        if (!empty($username) && !empty($password) && !empty($email) && !empty($imie) && !empty($nazwisko) && !empty($nr_tel)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO klienci (imie, nazwisko, nr_tel, ulica, numer_domu, miasto, kod_pocztowy, kraj, notatka) 
                VALUES (:imie, :nazwisko, :nr_tel, :ulica, :numer_domu, :miasto, :kod_pocztowy, :kraj, '')");
                $stmt->bindValue(':imie', $imie, PDO::PARAM_STR);
                $stmt->bindValue(':nazwisko', $nazwisko, PDO::PARAM_STR);
                $stmt->bindValue(':nr_tel', $nr_tel, PDO::PARAM_STR);
                $stmt->bindValue(':ulica', $ulica, PDO::PARAM_STR);
                $stmt->bindValue(':numer_domu', $numer_domu, PDO::PARAM_STR);
                $stmt->bindValue(':miasto', $miasto, PDO::PARAM_STR);
                $stmt->bindValue(':kod_pocztowy', $kod_pocztowy, PDO::PARAM_STR);
                $stmt->bindValue(':kraj', $kraj, PDO::PARAM_STR);
                $stmt->execute();
                $id_klienta = $pdo->lastInsertId();
              
                $stmt = $pdo->prepare("INSERT INTO konta (username, password, email, admin, id_klienta) VALUES (:username, :password, :email, :admin, :id_klienta)");
                $stmt->bindValue(':username', $username, PDO::PARAM_STR);
                $stmt->bindValue(':password', $password, PDO::PARAM_STR);
                $stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
                $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);
                $stmt->execute();
              
                $pdo->commit();
                echo "<div class='alert alert-success'>Konto zostało dodane.</div>";
            } catch (Exception $e) {
                $pdo->rollBack();
                echo "<div class='alert alert-danger'>Wystąpił błąd: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Uzupełnij wszystkie wymagane pola!</div>";
        }
    }
}
if (isset($_GET['usun'])) {
    $id = intval($_GET['usun']);    
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id_klienta FROM konta WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $id_klienta = $stmt->fetchColumn();

        if ($id_klienta) {

            $stmt = $pdo->prepare("DELETE FROM konta WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $pdo->prepare("DELETE FROM klienci WHERE id_klienta = :id_klienta");
            $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);
            $stmt->execute();
        }

        $pdo->commit();
        echo "<div class='alert alert-success'>Konto i powiązany klient zostali usunięci.</div>";
    } catch (Exception $e) {

        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Wystąpił błąd podczas usuwania: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_konto'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $admin = isset($_POST['admin']) ? 1 : 0;
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $nr_tel = $_POST['nr_tel'];
    $ulica = $_POST['ulica'];
    $numer_domu = $_POST['numer_domu'];
    $miasto = $_POST['miasto'];
    $kod_pocztowy = $_POST['kod_pocztowy'];
    $kraj = $_POST['kraj'];

    $phone_error = null;
    if (!preg_match('/^\d{9}$|^\+\d{11}$/', $nr_tel)) {
        $phone_error = "Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+').";
    }
    $postal_code_error = null;
    if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
        $postal_code_error = "Kod pocztowy musi mieć format: 12-345.";
    }
    if ($phone_error || $postal_code_error) {
        echo "<div class='alert alert-danger'>";
        if ($phone_error) {
            echo "<p>" . htmlspecialchars($phone_error) . "</p>";
        }
        if ($postal_code_error) {
            echo "<p>" . htmlspecialchars($postal_code_error) . "</p>";
        }
        echo "</div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_klienta FROM konta WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $id_klienta = $stmt->fetchColumn();

            if (!$id_klienta) {
                throw new Exception("Nie znaleziono klienta powiązanego z tym kontem.");
            }
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE klienci SET imie = :imie, nazwisko = :nazwisko, nr_tel = :nr_tel, ulica = :ulica, numer_domu = :numer_domu, miasto = :miasto, kod_pocztowy = :kod_pocztowy, kraj = :kraj 
              WHERE id_klienta = :id_klienta");
            $stmt->bindValue(':imie', $imie, PDO::PARAM_STR);
            $stmt->bindValue(':nazwisko', $nazwisko, PDO::PARAM_STR);
            $stmt->bindValue(':nr_tel', $nr_tel, PDO::PARAM_STR);
            $stmt->bindValue(':ulica', $ulica, PDO::PARAM_STR);
            $stmt->bindValue(':numer_domu', $numer_domu, PDO::PARAM_STR);
            $stmt->bindValue(':miasto', $miasto, PDO::PARAM_STR);
            $stmt->bindValue(':kod_pocztowy', $kod_pocztowy, PDO::PARAM_STR);
            $stmt->bindValue(':kraj', $kraj, PDO::PARAM_STR);
            $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $pdo->prepare("UPDATE konta SET username = :username, email = :email, admin = :admin WHERE id = :id");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':admin', $admin, PDO::PARAM_INT);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit();
            echo "<div class='alert alert-success'>Konto zostało zaktualizowane.</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Wystąpił błąd: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

$stmt = $pdo->query("SELECT * FROM konta ORDER BY id DESC");
$kont = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4">Zarządzanie kontami</h2>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="username" class="form-label">Nazwa użytkownika</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Hasło</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Min 8 znaków, 1 duża, 1 mała litera 1 cyfra i 1 znak specjalny" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="imie" class="form-label">Imię</label>
            <input type="text" id="imie" name="imie" class="form-control" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
        </div>
        <div class="mb-3">
            <label for="nazwisko" class="form-label">Nazwisko</label>
            <input type="text" id="nazwisko" name="nazwisko" class="form-control" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
        </div>
        <div class="mb-3">
            <label for="nr_tel" class="form-label">Numer telefonu</label>
            <input type="text" id="nr_tel" name="nr_tel" class="form-control" placeholder="Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+')" required>
        </div>
        <div class="mb-3">
          <label for="ulica" class="form-label">Ulica</label>
          <input type="text" id="ulica" name="ulica" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="numer_domu" class="form-label">Numer domu</label>
          <input type="text" id="numer_domu" name="numer_domu" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="miasto" class="form-label">Miasto</label>
          <input type="text" id="miasto" name="miasto" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="kod_pocztowy" class="form-label">Kod pocztowy</label>
          <input type="text" id="kod_pocztowy" name="kod_pocztowy" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="kraj" class="form-label">Kraj</label>
          <input type="text" id="kraj" name="kraj" class="form-control" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" id="admin" name="admin" class="form-check-input">
            <label for="admin" class="form-check-label">Administrator</label>
        </div>
        <button type="submit" name="dodaj_konto" class="btn btn-primary">Dodaj konto</button>
    </form>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nazwa użytkownika</th>
                <th>Email</th>
                <th>Administrator</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kont as $konto):            
             $stmt = $pdo->prepare("SELECT * FROM klienci WHERE id_klienta = :id_klienta");
             $stmt->bindValue(':id_klienta', $konto['id_klienta'], PDO::PARAM_INT);
             $stmt->execute();
             $klient = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
                <tr>
                    <td><?= htmlspecialchars($konto['username']) ?></td>
                    <td><?= htmlspecialchars($konto['email']) ?></td>
                    <td><?= $konto['admin'] ? 'Tak' : 'Nie' ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $konto['id'] ?>">
                            Edytuj
                        </button>
                        <div class="modal fade" id="editModal<?= $konto['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edytuj konto</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id" value="<?= $konto['id'] ?>">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Nazwa użytkownika</label>
                                                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($konto['username']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($konto['email']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="imie" class="form-label">Imię</label>
                                                <input type="text" id="imie" name="imie" class="form-control" value="<?= htmlspecialchars($klient['imie'])?>" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="nazwisko" class="form-label">Nazwisko</label>
                                                <input type="text" id="nazwisko" name="nazwisko" class="form-control" value="<?= htmlspecialchars($klient['nazwisko'])?>" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="nr_tel" class="form-label">Numer telefonu</label>
                                                <input type="text" id="nr_tel" name="nr_tel" class="form-control" value="<?= htmlspecialchars($klient['nr_tel']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                              <label for="ulica" class="form-label">Ulica</label>
                                              <input type="text" id="ulica" name="ulica" class="form-control" value="<?= htmlspecialchars($klient['ulica']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                              <label for="numer_domu" class="form-label">Numer domu</label>
                                              <input type="text" id="numer_domu" name="numer_domu" class="form-control" value="<?= htmlspecialchars($klient['numer_domu']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                              <label for="miasto" class="form-label">Miasto</label>
                                              <input type="text" id="miasto" name="miasto" class="form-control" value="<?= htmlspecialchars($klient['miasto']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                              <label for="kod_pocztowy" class="form-label">Kod pocztowy</label>
                                              <input type="text" id="kod_pocztowy" name="kod_pocztowy" class="form-control" value="<?= htmlspecialchars($klient['kod_pocztowy']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                              <label for="kraj" class="form-label">Kraj</label>
                                              <input type="text" id="kraj" name="kraj" class="form-control" value="<?= htmlspecialchars($klient['kraj']) ?>" required>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input type="checkbox" name="admin" class="form-check-input" <?= $konto['admin'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">Administrator</label>
                                            </div>
                                            <button type="submit" name="edytuj_konto" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="?page=konta&usun=<?= $konto['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć to konto?");
}
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować to konto?");
}
</script>











