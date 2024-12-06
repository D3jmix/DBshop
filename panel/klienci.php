<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_klienta'])) {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $telefon = $_POST['telefon'];
    $ulica = $_POST['ulica'];
    $numer_domu = $_POST['numer_domu'];
    $miasto = $_POST['miasto'];
    $kod_pocztowy = $_POST['kod_pocztowy'];
    $kraj = $_POST['kraj'] ?? 'Polska';
    $notatka = $_POST['notatka'] ?? '';

    $phone_error = null;
    if (!preg_match('/^\d{9}$|^\+\d{11}$/', $telefon)) {
        $phone_error = "Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+').";
    }

    $kod_pocztowy_error = null;
    if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
      $kod_pocztowy_error = "Kod pocztowy musi mieć format XX-XXX, np. 11-250.";
    }

    if ($phone_error || $kod_pocztowy_error) {
    echo "<div class='alert alert-danger'>";
    if ($phone_error) {
        echo "<p>" . htmlspecialchars($phone_error) . "</p>";
    }
    if ($kod_pocztowy_error) {
        echo "<p>" . htmlspecialchars($kod_pocztowy_error) . "</p>";
    }
    echo "</div>";    
    } else {
        if (!empty($imie) && !empty($nazwisko) && !empty($telefon) && !empty($ulica) && !empty($numer_domu) && !empty($miasto) && !empty($kod_pocztowy)) {
            $stmt = $pdo->prepare("INSERT INTO klienci (imie, nazwisko, nr_tel, ulica, numer_domu, miasto, kod_pocztowy, kraj, notatka) VALUES (:imie, :nazwisko, :telefon, :ulica, :numer_domu, :miasto, :kod_pocztowy, 
              :kraj, :notatka)");
            $stmt->bindValue(':imie', $imie, PDO::PARAM_STR);
            $stmt->bindValue(':nazwisko', $nazwisko, PDO::PARAM_STR);
            $stmt->bindValue(':telefon', $telefon, PDO::PARAM_STR);
            $stmt->bindValue(':ulica', $ulica, PDO::PARAM_STR);
            $stmt->bindValue(':numer_domu', $numer_domu, PDO::PARAM_STR);
            $stmt->bindValue(':miasto', $miasto, PDO::PARAM_STR);
            $stmt->bindValue(':kod_pocztowy', $kod_pocztowy, PDO::PARAM_STR);
            $stmt->bindValue(':kraj', $kraj, PDO::PARAM_STR);
            $stmt->bindValue(':notatka', $notatka, PDO::PARAM_STR);

            if ($stmt->execute()) {
                echo "<div class='alert alert-success'>Nowy klient został dodany.</div>";
            } else {
                echo "<div class='alert alert-danger'>Wystąpił błąd podczas dodawania klienta.</div>";
            }
        } else {
            echo "<div class='alert alert-warning'>Uzupełnij wszystkie wymagane pola!</div>";
        }
    }
}

if (isset($_GET['usun'])) {
    $id_klienta = intval($_GET['usun']);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM konta WHERE id_klienta = :id_klienta");
        $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $pdo->prepare("DELETE FROM klienci WHERE id_klienta = :id_klienta");
        $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);
        $stmt->execute();

        $pdo->commit();
        echo "<div class='alert alert-success'>Klient oraz powiązane konto zostały usunięte.</div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div class='alert alert-danger'>Wystąpił błąd podczas usuwania klienta: {$e->getMessage()}</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_klienta'])) {
    $id_klienta = $_POST['id_klienta'];
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $telefon = $_POST['telefon'];
    $ulica = $_POST['ulica'];
    $numer_domu = $_POST['numer_domu'];
    $miasto = $_POST['miasto'];
    $kod_pocztowy = $_POST['kod_pocztowy'];
    $kraj = $_POST['kraj'];
    $notatka = $_POST['notatka'] ?? '';

    $phone_error = null;
    if (!preg_match('/^\d{9}$|^\+\d{11}$/', $telefon)) {
        $phone_error = "Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+').";
    }

    $kod_pocztowy_error = null;
    if (!preg_match('/^\d{2}-\d{3}$/', $kod_pocztowy)) {
        $kod_pocztowy_error = "Kod pocztowy musi mieć format XX-XXX, np. 11-250.";
    }

    if ($phone_error || $kod_pocztowy_error) {
        echo "<div class='alert alert-danger'>";
        if ($phone_error) {
            echo "<p>" . htmlspecialchars($phone_error) . "</p>";
        }
        if ($kod_pocztowy_error) {
            echo "<p>" . htmlspecialchars($kod_pocztowy_error) . "</p>";
        }
        echo "</div>";
    } else {
        $stmt = $pdo->prepare("UPDATE klienci SET imie = :imie, nazwisko = :nazwisko, nr_tel = :telefon, ulica = :ulica, numer_domu = :numer_domu, miasto = :miasto, kod_pocztowy = :kod_pocztowy, kraj = :kraj, 
            notatka = :notatka WHERE id_klienta = :id_klienta");
        $stmt->bindValue(':imie', $imie, PDO::PARAM_STR);
        $stmt->bindValue(':nazwisko', $nazwisko, PDO::PARAM_STR);
        $stmt->bindValue(':telefon', $telefon, PDO::PARAM_STR);
        $stmt->bindValue(':ulica', $ulica, PDO::PARAM_STR);
        $stmt->bindValue(':numer_domu', $numer_domu, PDO::PARAM_STR);
        $stmt->bindValue(':miasto', $miasto, PDO::PARAM_STR);
        $stmt->bindValue(':kod_pocztowy', $kod_pocztowy, PDO::PARAM_STR);
        $stmt->bindValue(':kraj', $kraj, PDO::PARAM_STR);
        $stmt->bindValue(':notatka', $notatka, PDO::PARAM_STR);
        $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Dane klienta zostały zaktualizowane.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas aktualizacji danych klienta.</div>";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edytuj_konto') {
    $id_klienta = $_POST['id_klienta'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    $stmt = $pdo->prepare("UPDATE konta SET username = :username, email = :email WHERE id_klienta = :id_klienta");
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':id_klienta', $id_klienta, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Dane konta zostały zaktualizowane.</div>";
    } else {
        echo "<div class='alert alert-danger'>Wystąpił błąd podczas aktualizacji danych konta.</div>";
    }
}

$stmt = $pdo->query("SELECT id_klienta, imie, nazwisko, nr_tel, ulica, numer_domu, miasto, kod_pocztowy, kraj, notatka FROM klienci ORDER BY id_klienta DESC");
$klienci = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4">Zarządzanie klientami</h2>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="imie" class="form-label">Imię</label>
            <input type="text" id="imie" name="imie" class="form-control" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
        </div>
        <div class="mb-3">
            <label for="nazwisko" class="form-label">Nazwisko</label>
            <input type="text" id="nazwisko" name="nazwisko" class="form-control" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
        </div>
        <div class="mb-3">
            <label for="telefon" class="form-label">Telefon</label>
            <input type="text" id="telefon" name="telefon" class="form-control" placeholder="Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+')" required>
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
            <input type="text" id="kod_pocztowy" name="kod_pocztowy" class="form-control" pattern="\d{2}-\d{3}" required>
        </div>
        <div class="mb-3">
            <label for="kraj" class="form-label">Kraj</label>
            <input type="text" id="kraj" name="kraj" class="form-control" value="Polska">
        </div>
        <div class="mb-3">
            <label for="notatka" class="form-label">Notatka</label>
            <textarea id="notatka" name="notatka" class="form-control" rows="3" placeholder="(opcjonalne)"></textarea>
        </div>
        <button type="submit" name="dodaj_klienta" class="btn btn-primary">Dodaj klienta</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Imię</th>
                <th>Nazwisko</th>
                <th>Telefon</th>
                <th>Ulica</th>
                <th>Nr Domu</th>
                <th>Miasto</th>
                <th>Ma konto?</th>
                <th>Notatka</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($klienci as $klient): ?>
            <?php
                $stmt = $pdo->prepare("SELECT * FROM konta WHERE id_klienta = :id_klienta");
                $stmt->bindValue(':id_klienta', $klient['id_klienta'], PDO::PARAM_INT);
                $stmt->execute();
                $konto = $stmt->fetch(PDO::FETCH_ASSOC);
                $ma_konto = $konto ? true : false;
                ?>
                <tr>
                    <td><?= htmlspecialchars($klient['imie']) ?></td>
                    <td><?= htmlspecialchars($klient['nazwisko']) ?></td>
                    <td><?= htmlspecialchars($klient['nr_tel']) ?></td>
                    <td><?= htmlspecialchars($klient['ulica']) ?></td>
                    <td><?= htmlspecialchars($klient['numer_domu']) ?></td>
                    <td><?= htmlspecialchars($klient['miasto']) ?></td>
                    <td>
                        <?php if ($ma_konto): ?>
                            Tak
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#accountInfoModal<?= $klient['id_klienta'] ?>">Info</button>
                        <?php else: ?>
                            Nie
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($klient['notatka']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $klient['id_klienta'] ?>">
                            Edytuj
                        </button>
                      <div class="modal fade" id="editModal<?= $klient['id_klienta'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <h5 class="modal-title" id="editModalLabel">Edytuj klienta</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <form method="POST">
                                <input type="hidden" name="id_klienta" value="<?= $klient['id_klienta'] ?>">
                                
                                <div class="mb-3">
                                  <label for="imie" class="form-label">Imię</label>
                                  <input type="text" name="imie" value="<?= htmlspecialchars($klient['imie']) ?>" class="form-control" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="nazwisko" class="form-label">Nazwisko</label>
                                  <input type="text" name="nazwisko" value="<?= htmlspecialchars($klient['nazwisko']) ?>" class="form-control" pattern="[A-Za-zĄąĆćĘęŁłŃńÓóŚśŹźŻż]+" required>
                                </div>
                                <div class="mb-3">
                                  <label for="ulica" class="form-label">Ulica</label>
                                  <input type="text" name="ulica" value="<?= htmlspecialchars($klient['ulica']) ?>" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="numer_domu" class="form-label">Numer domu</label>
                                  <input type="text" name="numer_domu" value="<?= htmlspecialchars($klient['numer_domu']) ?>" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="miasto" class="form-label">Miasto</label>
                                  <input type="text" name="miasto" value="<?= htmlspecialchars($klient['miasto']) ?>" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="kod_pocztowy" class="form-label">Kod pocztowy</label>
                                  <input type="text" name="kod_pocztowy" value="<?= htmlspecialchars($klient['kod_pocztowy']) ?>" class="form-control" pattern="\d{2}-\d{3}" required>
                                </div>
                               
                                <div class="mb-3">
                                  <label for="kraj" class="form-label">Kraj</label>
                                  <input type="text" name="kraj" value="<?= htmlspecialchars($klient['kraj']) ?>" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="telefon" class="form-label">Telefon</label>
                                  <input type="text" name="telefon" value="<?= htmlspecialchars($klient['nr_tel']) ?>" class="form-control" placeholder="Numer telefonu musi składać się z dokładnie 9 cyfr lub 12 znaków (zaczynając od '+')" required>
                                  <?php if (!empty($phone_error)): ?>
                                  <div class="text-danger mt-1"><?= htmlspecialchars($phone_error) ?></div>
                                  <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                  <label for="notatka" class="form-label">Notatka</label>
                                  <textarea name="notatka" class="form-control" rows="3" placeholder="(opcjonalne)"><?= htmlspecialchars($klient['notatka']) ?></textarea>
                                </div>

                                <button type="submit" name="edytuj_klienta" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                              </form>
                            </div>
                          </div>
                        </div>
                      </div>
                        <a href="?page=klienci&usun=<?= $klient['id_klienta'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</a>
                    </td>
                   <?php if ($ma_konto): ?>
                    <div class="modal fade" id="accountInfoModal<?= $klient['id_klienta'] ?>" tabindex="-1" aria-labelledby="accountInfoModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="accountInfoModalLabel">Dane konta klienta</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="edytuj_konto">
                                        <input type="hidden" name="id_klienta" value="<?= $klient['id_klienta'] ?>">
                                        <p><strong>Nick:</strong> <?= htmlspecialchars($konto['username']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($konto['email']) ?></p>
                                        <p><strong>Data założenia:</strong> <?= htmlspecialchars($konto['created_at']) ?></p>

                                        <button type="button" class="btn btn-warning" data-bs-toggle="collapse" data-bs-target="#editAccountForm<?= $klient['id_klienta'] ?>">Edytuj</button>
                                        <div class="collapse" id="editAccountForm<?= $klient['id_klienta'] ?>">
                                            <div class="mb-3">
                                                <label for="username" class="form-label">Nick:</label>
                                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($konto['username']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email:</label>
                                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($konto['email']) ?>" required>
                                            </div>
                                            <button type="submit" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć tego klienta?");
}
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować tego klienta?");
}
</script>


