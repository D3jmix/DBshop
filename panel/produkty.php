<?php
include('db.php');

function walidujCeneIStan($cena, $stan) {
    if ($cena <= 0) {
        return "Cena musi być większa niż 0.";
    }
    if ($stan < 0) {
        return "Stan magazynowy nie może być ujemny.";
    }
    return true;
}
  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_produkt'])) {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];
    $cena = $_POST['cena'];
    $stan = $_POST['stan'];
    $id_kategorii = $_POST['id_kategorii'];
    $zdjecie = null;

    if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
        $uploadsDir = 'uploads/';
        $tmpName = $_FILES['zdjecie']['tmp_name'];
        $fileName = basename($_FILES['zdjecie']['name']);
        $filePath = $uploadsDir . uniqid() . '_' . $fileName;

        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }
        if (move_uploaded_file($tmpName, $filePath)) {
            $zdjecie = $filePath;
        } else {
            echo "<div class='alert alert-danger'>Nie udało się przesłać zdjęcia.</div>";
        }
    }   
    if (empty($zdjecie)) {
        echo "<div class='alert alert-warning'>Zdjęcie produktu jest wymagane. Proszę je dodać.</div>";
    } else {
      $walidacja = walidujCeneIStan($cena, $stan);
      if ($walidacja !== true) {
        echo "<div class='alert alert-warning'>$walidacja</div>";
      } else if (!empty($nazwa) && !empty($opis) && !empty($cena) && !empty($stan) && !empty($id_kategorii)) {
        try {
          $pdo->beginTransaction();
          
          $stmt = $pdo->prepare("INSERT INTO produkty (nazwa, opis, cena, stan, zdjecie, data_dodania) VALUES (:nazwa, :opis, :cena, :stan, :zdjecie, NOW())");
          $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
          $stmt->bindValue(':opis', $opis, PDO::PARAM_STR);
          $stmt->bindValue(':cena', $cena, PDO::PARAM_STR);
          $stmt->bindValue(':stan', $stan, PDO::PARAM_INT);
          $stmt->bindValue(':zdjecie', $zdjecie, PDO::PARAM_STR);
          $stmt->execute();
          
          $id_produktu = $pdo->lastInsertId();
          
          $stmt_kategorie = $pdo->prepare("INSERT INTO kategorie_k (id_produktu, id_kategorii) VALUES (:id_produktu, :id_kategorii)");
          foreach ($id_kategorii as $id_kat) {
            $stmt_kategorie->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
            $stmt_kategorie->bindValue(':id_kategorii', $id_kat, PDO::PARAM_INT);
            $stmt_kategorie->execute();
          }
          
          $pdo->commit();         
          echo "<div class='alert alert-success'>Produkt został dodany.</div>";
        } catch (Exception $e) {
          echo "<div class='alert alert-danger'>Wystąpił błąd podczas dodawania produktu.</div>";
        }
      } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
      }
   }
}
if (isset($_GET['usun'])) {
    $id_produktu = intval($_GET['usun']);
    if ($id_produktu > 0) {
        try {
            $pdo->beginTransaction();

            $stmt_kategorie = $pdo->prepare("DELETE FROM kategorie_k WHERE id_produktu = :id_produktu");
            $stmt_kategorie->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
            $stmt_kategorie->execute();

            $stmt_produkt = $pdo->prepare("DELETE FROM produkty WHERE id = :id_produktu");
            $stmt_produkt->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
            $stmt_produkt->execute();

            $pdo->commit();
            echo "<div class='alert alert-success'>Produkt został usunięty.</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas usuwania produktu o ID $id_produktu.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Nieprawidłowy identyfikator produktu.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_produkt'])) {
    $id_produktu = intval($_POST['id_produktu']);
    $nazwa = trim($_POST['nazwa']);
    $opis = trim($_POST['opis']);
    $cena = floatval($_POST['cena']);
    $stan = intval($_POST['stan']);
    $id_kategorii = $_POST['id_kategorii'] ?? [];
    $zdjecie = null;

    if (empty($nazwa) || empty($opis) || $cena <= 0 || $stan < 0 || empty($id_kategorii)) {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola poprawnie!</div>";
    } else {
        try {
            $pdo->beginTransaction();

            if (isset($_FILES['zdjecie']) && $_FILES['zdjecie']['error'] === UPLOAD_ERR_OK) {
                $uploadsDir = 'uploads/';
                $tmpName = $_FILES['zdjecie']['tmp_name'];
                $fileName = basename($_FILES['zdjecie']['name']);
                $filePath = $uploadsDir . uniqid() . '_' . $fileName;

                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0777, true);
                }
                if (move_uploaded_file($tmpName, $filePath)) {
                    $zdjecie = $filePath;

                    $stmt = $pdo->prepare("UPDATE produkty SET zdjecie = :zdjecie WHERE id = :id_produktu");
                    $stmt->bindValue(':zdjecie', $zdjecie, PDO::PARAM_STR);
                    $stmt->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
                    $stmt->execute();
                } else {
                    echo "<div class='alert alert-danger'>Nie udało się przesłać nowego zdjęcia. Istniejące zdjęcie zostaje bez zmian.</div>";
                }
            }

            $stmt = $pdo->prepare("UPDATE produkty SET nazwa = :nazwa, opis = :opis, cena = :cena, stan = :stan WHERE id = :id_produktu");
            $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
            $stmt->bindValue(':opis', $opis, PDO::PARAM_STR);
            $stmt->bindValue(':cena', $cena, PDO::PARAM_STR);
            $stmt->bindValue(':stan', $stan, PDO::PARAM_INT);
            $stmt->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
            $stmt->execute();

            $stmt = $pdo->prepare("DELETE FROM kategorie_k WHERE id_produktu = :id_produktu");
            $stmt->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
            $stmt->execute();

            $stmt_kategorie = $pdo->prepare("INSERT INTO kategorie_k (id_produktu, id_kategorii) VALUES (:id_produktu, :id_kategorii)");
            foreach ($id_kategorii as $id_kat) {
                $stmt_kategorie->bindValue(':id_produktu', $id_produktu, PDO::PARAM_INT);
                $stmt_kategorie->bindValue(':id_kategorii', intval($id_kat), PDO::PARAM_INT);
                $stmt_kategorie->execute();
            }

            $pdo->commit();
            echo "<div class='alert alert-success'>Produkt został zaktualizowany.</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas edytowania produktu: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}


$stmt = $pdo->query("SELECT * FROM Kategorie");
$kategorie = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT p.*, GROUP_CONCAT(k.nazwa SEPARATOR ', ') AS kategorie FROM produkty p LEFT JOIN kategorie_k kk ON p.id = kk.id_produktu LEFT JOIN Kategorie k ON kk.id_kategorii = k.id_kategorii
  GROUP BY p.id ORDER BY p.id DESC");
$produkty = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
    <h2 class="mb-4">Zarządzanie produktami</h2>

    <form method="POST" class="mb-4" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="nazwa" class="form-label">Nazwa produktu</label>
            <input type="text" id="nazwa" name="nazwa" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="opis" class="form-label">Opis</label>
            <textarea id="opis" name="opis" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="cena" class="form-label">Cena</label>
            <input type="number" step="0.01" id="cena" name="cena" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="stan" class="form-label">Stan magazynowy</label>
            <input type="number" id="stan" name="stan" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="kategoria" class="form-label">Kategoria</label>
            <select id="kategoria" name="id_kategorii[]" class="form-control" multiple required>
                <?php foreach ($kategorie as $kategoria): ?>
                    <option value="<?= htmlspecialchars($kategoria['id_kategorii']) ?>"><?= htmlspecialchars($kategoria['nazwa']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="zdjecie" class="form-label">Zdjęcie produktu</label>
            <input type="file" id="zdjecie" name="zdjecie" class="form-control" accept="image/*">
        </div>
        <button type="submit" name="dodaj_produkt" class="btn btn-primary">Dodaj produkt</button>
    </form>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nazwa</th>
                <th>Opis</th>
                <th>Cena</th>
                <th>Stan</th>
                <th>Kategorie</th>
                <th>Zdjęcie</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produkty as $produkt): ?>
                <tr>
                    <td><?= htmlspecialchars($produkt['id']) ?></td>
                    <td><?= htmlspecialchars($produkt['nazwa']) ?></td>
                    <td><?= htmlspecialchars($produkt['opis']) ?></td>
                    <td><?= htmlspecialchars($produkt['cena']) ?></td>
                    <td><?= htmlspecialchars($produkt['stan']) ?></td>
                    <td><?= htmlspecialchars($produkt['kategorie']) ?></td>
                    <td>
                        <?php if (!empty($produkt['zdjecie'])): ?>
                            <img src="<?= htmlspecialchars($produkt['zdjecie']) ?>" alt="Zdjęcie produktu" style="width: 100px; height: auto;">
                        <?php else: ?>
                            Brak zdjęcia
                        <?php endif; ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $produkt['id'] ?>">
                            Edytuj
                        </button>
                        <div class="modal fade" id="editModal<?= $produkt['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edytuj produkt</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id_produktu" value="<?= $produkt['id'] ?>">
                                            <div class="mb-3">
                                                <label for="nazwa" class="form-label">Nazwa</label>
                                                <input type="text" name="nazwa" class="form-control" value="<?= htmlspecialchars($produkt['nazwa']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="opis" class="form-label">Opis</label>
                                                <textarea name="opis" class="form-control" required><?= htmlspecialchars($produkt['opis']) ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="cena" class="form-label">Cena</label>
                                                <input type="number" step="0.01" name="cena" class="form-control" value="<?= htmlspecialchars($produkt['cena']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="stan" class="form-label">Stan magazynowy</label>
                                                <input type="number" name="stan" class="form-control" value="<?= htmlspecialchars($produkt['stan']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="id_kategorii" class="form-label">Kategorie</label>
                                                <div id="id_kategorii">
                                                  <?php foreach ($kategorie as $kategoria): ?>
                                                  <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="kategoria_<?= htmlspecialchars($kategoria['id_kategorii']) ?>" name="id_kategorii[]" value="<?= htmlspecialchars($kategoria['id_kategorii']) ?>">
                                                    <label class="form-check-label" for="kategoria_<?= htmlspecialchars($kategoria['id_kategorii']) ?>"><?= htmlspecialchars($kategoria['nazwa']) ?></label>
                                                  </div>
                                                  <?php endforeach; ?>
                                              </div>
                                            <button type="submit" name="edytuj_produkt" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                                          </div>
                                         </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                       <a href="?page=produkty&usun=<?= htmlspecialchars($produkt['id']) ?>" class="btn btn-danger btn-sm">Usuń</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć ten produkt?");
}
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować ten produkt?");
}
</script>

