<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_platnosc'])) {
    $nazwa = $_POST['nazwa'];

    if (!empty($nazwa)) {
        $stmt = $pdo->prepare("INSERT INTO sposob_platnosci (nazwa) VALUES (:nazwa)");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Metoda płatności została dodana.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas dodawania metody płatności.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
    }
}

if (isset($_GET['usun'])) {
    $id_platnosc = intval($_GET['usun']);
    
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM zamowienia WHERE szczegoly_platnosci = :id_platnosc");
    $stmt_check->bindValue(':id_platnosc', $id_platnosc, PDO::PARAM_INT);
    $stmt_check->execute();
    $count = $stmt_check->fetchColumn();
  
    if ($count > 0) {
        echo "<div class='alert alert-warning'>Nie możesz usunąć tego sposobu płatności, ponieważ jest przypisany do istniejących zamówień.</div>";
    } else {
        $stmt_delete = $pdo->prepare("DELETE FROM sposob_platnosci WHERE id_platnosc = :id_platnosc");
        $stmt_delete->bindValue(':id_platnosc', $id_platnosc, PDO::PARAM_INT);
        if ($stmt_delete->execute()) {
            echo "<div class='alert alert-success'>Metoda płatności została usunięta.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas usuwania metody płatności.</div>";
        }
    }  
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_platnosc'])) {
    $id_platnosc = $_POST['id_platnosc'];
    $nazwa = $_POST['nazwa'];

    if (!empty($nazwa)) {
        $stmt = $pdo->prepare("UPDATE sposob_platnosci SET nazwa = :nazwa WHERE id_platnosc = :id_platnosc");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':id_platnosc', $id_platnosc, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Metoda płatności została zaktualizowana.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas edytowania metody płatności.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
    }
}
$stmt = $pdo->query("SELECT * FROM sposob_platnosci ORDER BY id_platnosc DESC");
$platnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
    <h2 class="mb-4">Zarządzanie metodami płatności</h2>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="nazwa" class="form-label">Nazwa</label>
            <input type="text" id="nazwa" name="nazwa" class="form-control" required>
        </div>
        <button type="submit" name="dodaj_platnosc" class="btn btn-primary">Dodaj metodę płatności</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nazwa</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($platnosci as $platnosc): ?>
                <tr>
                    <td><?= htmlspecialchars($platnosc['nazwa']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $platnosc['id_platnosc'] ?>">
                            Edytuj
                        </button>
                        <div class="modal fade" id="editModal<?= $platnosc['id_platnosc'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edytuj metodę płatności</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id_platnosc" value="<?= $platnosc['id_platnosc'] ?>">
                                            <div class="mb-3">
                                                <label for="nazwa" class="form-label">Nazwa</label>
                                                <input type="text" name="nazwa" class="form-control" value="<?= htmlspecialchars($platnosc['nazwa']) ?>" required>
                                            </div>
                                            <button type="submit" name="edytuj_platnosc" class="btn btn-primary" onclick="return confirmEdit()">Zapisz zmiany</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="?page=platnosc&usun=<?= $platnosc['id_platnosc'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć ten sposób płatności?");
}
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować ten sposób płatności?");
}
</script>

