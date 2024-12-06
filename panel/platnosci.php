<?php
session_start();
require_once 'db.php';

$zalogowany = false;
$username = '';
$message = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT username, id_klienta FROM konta WHERE id = :id");
    $stmt->bindValue(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $zalogowany = true;
        $username = $user['username'];
        $klientId = $user['id_klienta'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['wyloguj'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Sprawdzamy, czy użytkownik jest zalogowany
if (!$zalogowany) {
    header("Location: login.php");
    exit;
}

function walidacjaKarta($numer_karty, $data_waznosci, $cvv, $nazwa_posiadacza)
{
    $errors = [];
    try {
        if (!preg_match('/^\d{16}$/', $numer_karty)) {
            $errors[] = "Numer karty musi zawierać dokładnie 16 cyfr.";
        }

        if (!preg_match('/^\d{3}$/', $cvv)) {
            $errors[] = "CVV musi zawierać dokładnie 3 cyfry.";
        }

        if (!preg_match('/^[A-Za-z]+ [A-Za-z]+$/', $nazwa_posiadacza)) {
            $errors[] = "Nazwa posiadacza musi składać się z imienia i nazwiska.";
        }

        $current_date = new DateTime();
        $expiry_date = DateTime::createFromFormat('Y-m-d', $data_waznosci);
        if (!$expiry_date || $expiry_date < $current_date) {
            $errors[] = "Data ważności nie może być wcześniejsza niż bieżący miesiąc.";
      }
    } catch (Exception $e) {
      $errors[] = $e->getMessage();
    }

    return $errors;
}


function walidacjaBlik($numer_telefonu)
{
    $errors = [];
    if (!preg_match('/^\d{9}$|^\+\d{11}$/', $numer_telefonu)) {
      $errors[] = "Numer telefonu musi zawierać dokładnie 9 cyfr lub zaczynać się od '+' i mieć 11 cyfr.";
    }
    return $errors;
}

// Obsługa formularza dodawania metody płatności
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_metode'])) {
    try {
        $pdo->beginTransaction(); // Rozpoczęcie transakcji

        $sposob_platnosci = $_POST['sposob_platnosci'];
        $numer_karty = $_POST['numer_karty'] ?? null;
        $data_waznosci = $_POST['data_waznosci'] ? $_POST['data_waznosci'] . '-01' : null;
        $cvv = $_POST['cvv'] ?? null;
        $nazwa_posiadacza = $_POST['nazwa_posiadacza'] ?? null;
        $numer_telefonu = $_POST['numer_telefonu'] ?? null;

        $errors = [];
        if ($sposob_platnosci === 'karta') {
            $errors = walidacjaKarta($numer_karty, $data_waznosci, $cvv, $nazwa_posiadacza);
        } elseif ($sposob_platnosci === 'blik') {
            $errors = walidacjaBlik($numer_telefonu);
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id_platnosc FROM sposob_platnosci WHERE nazwa = :nazwa");
            $stmt->bindValue(':nazwa', $sposob_platnosci, PDO::PARAM_STR);
            $stmt->execute();
            $platnosc = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($platnosc) {
                $id_platnosci = $platnosc['id_platnosc'];

                $stmt = $pdo->prepare("INSERT INTO metody_platnosci
                    (id_klienta, id_platnosci, numer_karty, data_waznosci, cvv, nazwa_posiadacza, numer_telefonu)
                    VALUES (:id_klienta, :id_platnosci, :numer_karty, :data_waznosci, :cvv, :nazwa_posiadacza, :numer_telefonu)");
                $stmt->execute([
                    ':id_klienta' => $klientId,
                    ':id_platnosci' => $id_platnosci,
                    ':numer_karty' => $numer_karty,
                    ':data_waznosci' => $data_waznosci,
                    ':cvv' => $cvv,
                    ':nazwa_posiadacza' => $nazwa_posiadacza,
                    ':numer_telefonu' => $numer_telefonu,
                ]);

                $pdo->commit(); // Zatwierdzenie transakcji
                $message = "Metoda płatności została dodana pomyślnie.";
            } else {
                throw new Exception("Wybrany sposób płatności jest nieprawidłowy.");
            }
        } else {
            throw new Exception(implode("<br>", $errors));
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Cofnięcie transakcji tylko jeśli była rozpoczęta
        }
        $message = "Błąd: " . $e->getMessage();
    }
}

// Pobieranie metod płatności użytkownika
$stmt = $pdo->prepare("SELECT mp.*, sp.nazwa AS sposob_platnosci
                       FROM metody_platnosci mp
                       JOIN sposob_platnosci sp ON mp.id_platnosci = sp.id_platnosc
                       WHERE mp.id_klienta = :id_klienta");
$stmt->execute([':id_klienta' => $klientId]);
$metody_platnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pobieranie dostępnych sposobów płatności
$stmt = $pdo->prepare("SELECT nazwa FROM sposob_platnosci");
$stmt->execute();
$sposoby_platnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['usun_metode'])) {
    $id_metody = $_POST['id_metody'] ?? null;
    try {
        if ($id_metody) {
            $pdo->beginTransaction(); // Rozpoczęcie transakcji

            $stmt = $pdo->prepare("DELETE FROM metody_platnosci WHERE id_metody = :id_metody");
            $stmt->bindValue(':id_metody', $id_metody, PDO::PARAM_INT);
            $stmt->execute();

            $pdo->commit(); // Zatwierdzenie transakcji
            $message = "Metoda płatności została usunięta!";
        } else {
            throw new Exception("Nie podano ID metody płatności do usunięcia.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack(); // Cofnięcie transakcji tylko jeśli była rozpoczęta
        }
        $message = "Błąd: " . $e->getMessage();
    }
}


// Obsługa edycji metody płatności
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_metode'])) {
    $id_metody = $_POST['id_metody'];
    $numer_karty = $_POST['numer_karty'] ?? null;
    $data_waznosci = $_POST['data_waznosci'] ? $_POST['data_waznosci'] . '-01' : null;
    $cvv = $_POST['cvv'] ?? null;
    $nazwa_posiadacza = $_POST['nazwa_posiadacza'] ?? null;
    $numer_telefonu = $_POST['numer_telefonu'] ?? null;

    $stmt = $pdo->prepare("SELECT s.nazwa FROM metody_platnosci mp
        JOIN sposob_platnosci s ON mp.id_platnosci = s.id_platnosc
        WHERE mp.id_metody = :id_metody");
    $stmt->bindValue(':id_metody', $id_metody, PDO::PARAM_INT);
    $stmt->execute();
    $sposob_platnosci = $stmt->fetchColumn();

    $errors = [];
    if ($sposob_platnosci === 'karta') {
        $errors = walidacjaKarta($numer_karty, $data_waznosci, $cvv, $nazwa_posiadacza);
    } elseif ($sposob_platnosci === 'blik') {
        $errors = walidacjaBlik($numer_telefonu);
    }

    if (empty($errors)) {
        if ($sposob_platnosci === 'karta') {
            $stmt = $pdo->prepare("UPDATE metody_platnosci SET numer_karty = ?, data_waznosci = ?, cvv = ?, nazwa_posiadacza = ? WHERE id_metody = ?");
            $stmt->execute([$numer_karty, $data_waznosci, $cvv, $nazwa_posiadacza, $id_metody]);
        } elseif ($sposob_platnosci === 'blik') {
            $stmt = $pdo->prepare("UPDATE metody_platnosci SET numer_telefonu = ? WHERE id_metody = ?");
            $stmt->execute([$numer_telefonu, $id_metody]);
        }
        $message = "Zmiany zostały zapisane.";
    } else {
        $message = implode("<br>", $errors);
    }
}
?>

  
    <div class="container">
        <h1 class="mt-5">Twoje metody płatności</h1>

        <?php if (!empty($message)): ?>
        <div class="alert alert-info mt-3"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Dodaj metodę płatności</button>

        <!-- Modal do dodawania metody płatności -->
        <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPaymentModalLabel">Dodaj metodę płatności</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="sposob_platnosci" class="form-label">Wybierz sposób płatności</label>
                                <select name="sposob_platnosci" id="sposob_platnosci" class="form-select">
                                    <?php foreach ($sposoby_platnosci as $sposob): ?>
                                       <?php if (!in_array($sposob['nazwa'], ['przelew', 'gotówka'])): // Ukryj przelew i gotówka ?>
                                           <option value="<?= htmlspecialchars($sposob['nazwa']) ?>"><?= htmlspecialchars($sposob['nazwa']) ?></option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="karta-fields" class="payment-fields" style="display: none;">
                                <!-- Pola karty -->
                                <div class="mb-3">
                                    <label for="numer_karty" class="form-label">Numer karty</label>
                                    <input type="text" name="numer_karty" id="numer_karty" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="data_waznosci" class="form-label">Data ważności</label>
                                    <input type="month" name="data_waznosci" id="data_waznosci" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" name="cvv" id="cvv" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label for="nazwa_posiadacza" class="form-label">Nazwa posiadacza</label>
                                    <input type="text" name="nazwa_posiadacza" id="nazwa_posiadacza" class="form-control">
                                </div>
                            </div>

                            <div id="blik-fields" class="payment-fields" style="display: none;">
                                <!-- Pola blik -->
                                <div class="mb-3">
                                    <label for="numer_telefonu" class="form-label">Numer telefonu (BLIK)</label>
                                    <input type="text" name="numer_telefonu" id="numer_telefonu" class="form-control">
                                </div>
                            </div>

                            <button type="submit" name="dodaj_metode" class="btn btn-primary mt-3">Dodaj metodę</button>
                        </form>
                    </div>
                </div>
          </div>
        </div>

        <!-- Wyświetlanie metod płatności użytkownika -->
        <?php if (!empty($metody_platnosci)): ?>
            <?php foreach ($metody_platnosci as $metoda): ?>
                <div class="card mt-3">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($metoda['sposob_platnosci']) ?></h5>
                        <p class="card-text">
                            <?php if ($metoda['sposob_platnosci'] == 'karta'): ?>
                                Numer karty: <?= htmlspecialchars($metoda['numer_karty']) ?><br>
                                Data ważności: <?= htmlspecialchars($metoda['data_waznosci']) ?><br>
                                CVV: <?= htmlspecialchars($metoda['cvv']) ?><br>
                                Nazwa posiadacza: <?= htmlspecialchars($metoda['nazwa_posiadacza']) ?>
                            <?php elseif ($metoda['sposob_platnosci'] == 'blik'): ?>
                                Numer telefonu: <?= htmlspecialchars($metoda['numer_telefonu']) ?>
                            <?php endif; ?>
                        </p>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editPaymentModal<?= $metoda['id_metody'] ?>">Edytuj</button>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id_metody" value="<?= $metoda['id_metody'] ?>">
                            <button type="submit" name="usun_metode" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</button>
                        </form>
                    </div>
                </div>

                <!-- Modal do edytowania metody płatności -->
                <div class="modal fade" id="editPaymentModal<?= $metoda['id_metody'] ?>" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="editPaymentModalLabel">Edytuj metodę płatności</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
              <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="id_metody" value="<?= $metoda['id_metody'] ?>">
                    <div class="mb-3">
                        <label for="edit_sposob_platnosci" class="form-label">Wybierz sposób płatności</label>
                        <!-- Zmiana na input readonly -->
                        <input type="text" id="edit_sposob_platnosci<?= $metoda['id_metody'] ?>" value="<?= htmlspecialchars($metoda['sposob_platnosci']) ?>" class="form-control" readonly>
                    </div>

                    <!-- Pola formularza dla karty -->
                    <div id="karta-edit-fields<?= $metoda['id_metody'] ?>" class="payment-fields-edit<?= $metoda['id_metody'] ?>" style="display: none;">
                        <div class="mb-3">
                            <label for="numer_karty" class="form-label">Numer karty</label>
                            <input type="text" name="numer_karty" id="numer_karty" value="<?= htmlspecialchars($metoda['numer_karty']) ?>" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="data_waznosci" class="form-label">Data ważności</label>
                            <input type="month" name="data_waznosci" value="<?= htmlspecialchars(substr($metoda['data_waznosci'], 0, 7)) ?>" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="cvv" class="form-label">CVV</label>
                            <input type="text" name="cvv" value="<?= htmlspecialchars($metoda['cvv']) ?>" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="nazwa_posiadacza" class="form-label">Nazwa posiadacza</label>
                            <input type="text" name="nazwa_posiadacza" value="<?= htmlspecialchars($metoda['nazwa_posiadacza']) ?>" class="form-control">
                        </div>
                    </div>

                    <!-- Pola formularza dla Blik -->
                    <div id="blik-edit-fields<?= $metoda['id_metody'] ?>" class="payment-fields-edit<?= $metoda['id_metody'] ?>" style="display: none;">
                        <div class="mb-3">
                            <label for="numer_telefonu" class="form-label">Numer telefonu (Blik)</label>
                            <input type="text" name="numer_telefonu" value="<?= htmlspecialchars($metoda['numer_telefonu']) ?>" class="form-control">
                        </div>
                    </div>

                  <button type="submit" name="edit_metode" class="btn btn-primary mt-3">Zapisz zmiany</button>
                </form>
            </div>
        </div>
    </div>
</div>

        <?php endforeach; ?>
        <?php else: ?>
        <p>Brak dodanych metod płatności.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sposob_platnosci').addEventListener('change', function() {
            var selected = this.value;
            document.querySelectorAll('.payment-fields').forEach(function(field) {
                field.style.display = 'none';
            });
            if (selected === 'karta') {
                document.getElementById('karta-fields').style.display = 'block';
            } else if (selected === 'blik') {
                document.getElementById('blik-fields').style.display = 'block';
            } else if (selected === 'przelew') {
                document.getElementById('przelew-fields').style.display = 'block';
            }
        });

        document.getElementById('sposob_platnosci').value = 'karta';
      document.getElementById('karta-fields').style.display = 'block';
      
      function toggleEditFields(id) {
        var selected = document.getElementById('edit_sposob_platnosci' + id).value;

        // Ukrywanie wszystkich pól
        document.querySelectorAll('.payment-fields-edit' + id).forEach(function(field) {
          field.style.display = 'none';
        });

        // Pokazywanie odpowiednich pól w zależności od wyboru
        if (selected === 'karta') {
          document.getElementById('karta-edit-fields' + id).style.display = 'block';
        } else if (selected === 'blik') {
          document.getElementById('blik-edit-fields' + id).style.display = 'block';
        } 
    }

    // Inicjalizacja po załadowaniu strony, dla każdego formularza
      document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($metody_platnosci as $metoda): ?>
            // Wywołujemy funkcję toggleEditFields przy starcie dla każdej metody płatności
            toggleEditFields(<?= $metoda['id_metody'] ?>);
        <?php endforeach; ?>
    });
      
    function confirmDelete() {
        return confirm('Czy na pewno chcesz usunąć tę metodę płatności?');
        }
    </script>


















