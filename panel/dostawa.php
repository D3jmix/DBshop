<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_dostawe'])) {
    $nazwa = $_POST['nazwa'];
    $koszt = $_POST['koszt'];

    if (!empty($nazwa) && !empty($koszt)) {
      if ($koszt < 0) {
            echo "<div class='alert alert-warning'>Koszt dostawy nie może być ujemny!</div>";
        } else {
        $stmt = $pdo->prepare("INSERT INTO sposob_dostawy (nazwa, koszt) VALUES (:nazwa, :koszt)");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':koszt', $koszt, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Dostawa została dodana.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas dodawania dostawy.</div>";
        }
      }
    } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
    }
}

if (isset($_GET['usun'])) {
    $id_dostawy = intval($_GET['usun']);
    
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM zamowienia WHERE sposob_dostawy = :id_dostawy");
    $stmt_check->bindValue(':id_dostawy', $id_dostawy, PDO::PARAM_INT);
    $stmt_check->execute();
    $count = $stmt_check->fetchColumn();
    
    if ($count > 0) {
        echo "<div class='alert alert-warning'>Nie możesz usunąć tej dostawy, ponieważ jest przypisana do istniejących zamówień.</div>";
    } else {
        $stmt_delete = $pdo->prepare("DELETE FROM sposob_dostawy WHERE id_dostawy = :id_dostawy");
        $stmt_delete->bindValue(':id_dostawy', $id_dostawy, PDO::PARAM_INT);
        if ($stmt_delete->execute()) {
            echo "<div class='alert alert-success'>Dostawa została usunięta.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas usuwania dostawy.</div>";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_dostawe'])) {
    $id_dostawy = $_POST['id_dostawy'];
    $nazwa = $_POST['nazwa'];
    $koszt = $_POST['koszt'];

    if (!empty($nazwa) && !empty($koszt)) {
      if ($koszt < 0) {
            echo "<div class='alert alert-warning'>Koszt dostawy nie może być ujemny!</div>";
      } else {
        $stmt = $pdo->prepare("UPDATE sposob_dostawy SET nazwa = :nazwa, koszt = :koszt WHERE id_dostawy = :id_dostawy");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':koszt', $koszt, PDO::PARAM_STR);
        $stmt->bindValue(':id_dostawy', $id_dostawy, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Dostawa została zaktualizowana.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas edytowania dostawy.</div>";
        }
      }
    } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
    }
}

$stmt = $pdo->query("SELECT * FROM sposob_dostawy ORDER BY id_dostawy DESC");
$dostawy = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4">Zarządzanie dostawami</h2>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="nazwa" class="form-label">Nazwa</label>
            <input type="text" id="nazwa" name="nazwa" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="koszt" class="form-label">Koszt</label>
            <input type="number" step="0.01" id="koszt" name="koszt" class="form-control" required>
        </div>
        <button type="submit" name="dodaj_dostawe" class="btn btn-primary">Dodaj dostawę</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nazwa</th>
                <th>Koszt</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dostawy as $dostawa): ?>
                <tr>
                    <td><?= htmlspecialchars($dostawa['nazwa']) ?></td>
                    <td><?= htmlspecialchars($dostawa['koszt']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $dostawa['id_dostawy'] ?>">
                            Edytuj
                        </button>

                        <div class="modal fade" id="editModal<?= $dostawa['id_dostawy'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edytuj dostawę</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id_dostawy" value="<?= $dostawa['id_dostawy'] ?>">
                                            <div class="mb-3">
                                                <label for="nazwa" class="form-label">Nazwa</label>
                                                <input type="text" name="nazwa" class="form-control" value="<?= htmlspecialchars($dostawa['nazwa']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="koszt" class="form-label">Koszt</label>
                                                <input type="number" step="0.01" name="koszt" class="form-control" value="<?= htmlspecialchars($dostawa['koszt']) ?>" required>
                                            </div>
                                            <button type="submit" name="edytuj_dostawe" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a href="?page=dostawa&usun=<?= $dostawa['id_dostawy'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć tę dostawę?");
}
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować tę dostawę?");
}
</script>
