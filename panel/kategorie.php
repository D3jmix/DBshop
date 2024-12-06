<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dodaj_kategorie'])) {
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];

    if (!empty($nazwa) && !empty($opis)) {
        $stmt = $pdo->prepare("INSERT INTO Kategorie (nazwa, opis) VALUES (:nazwa, :opis)");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':opis', $opis, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Kategoria została dodana.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas dodawania kategorii.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
    }
}

if (isset($_GET['usun'])) {
    $id_kategorii = intval($_GET['usun']);
    
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM kategorie_k WHERE id_kategorii = :id_kategorii");
    $stmt_check->bindValue(':id_kategorii',$id_kategorii, PDO::PARAM_INT);
    $stmt_check->execute();
    $count = $stmt_check->fetchColumn();
  
    if ($count > 0) {
        echo "<div class='alert alert-warning'>Nie możesz usunąć tej kategorii, ponieważ jest przypisana do istniejących produktów.</div>";
    } else {
        $stmt = $pdo->prepare("DELETE FROM Kategorie WHERE id_kategorii = :id_kategorii");
        $stmt->bindValue(':id_kategorii', $id_kategorii, PDO::PARAM_INT);
        if ($stmt->execute()) {
             echo "<div class='alert alert-success'>Kategoria została usunięta.</div>";
        } else {
             echo "<div class='alert alert-danger'>Wystąpił błąd podczas usuwania kategorii.</div>";
        }
    }
}
  
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edytuj_kategorie'])) {
    $id_kategorii = $_POST['id_kategorii'];
    $nazwa = $_POST['nazwa'];
    $opis = $_POST['opis'];

    if (!empty($nazwa) && !empty($opis)) {
        $stmt = $pdo->prepare("UPDATE Kategorie SET nazwa = :nazwa, opis = :opis WHERE id_kategorii = :id_kategorii");
        $stmt->bindValue(':nazwa', $nazwa, PDO::PARAM_STR);
        $stmt->bindValue(':opis', $opis, PDO::PARAM_STR);
        $stmt->bindValue(':id_kategorii', $id_kategorii, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Kategoria została zaktualizowana.</div>";
        } else {
            echo "<div class='alert alert-danger'>Wystąpił błąd podczas edytowania kategorii.</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Uzupełnij wszystkie pola!</div>";
    }
}

$stmt = $pdo->query("SELECT * FROM Kategorie ORDER BY id_kategorii DESC");
$kategorie = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <h2 class="mb-4">Zarządzanie kategoriami</h2>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="nazwa" class="form-label">Nazwa kategorii</label>
            <input type="text" id="nazwa" name="nazwa" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="opis" class="form-label">Opis kategorii</label>
            <textarea id="opis" name="opis" class="form-control" required></textarea>
        </div>
        <button type="submit" name="dodaj_kategorie" class="btn btn-primary">Dodaj kategorię</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nazwa</th>
                <th>Opis</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($kategorie as $kategoria): ?>
                <tr>
                    <td><?= htmlspecialchars($kategoria['nazwa']) ?></td>
                    <td><?= htmlspecialchars($kategoria['opis']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $kategoria['id_kategorii'] ?>">
                            Edytuj
                        </button>

                        <div class="modal fade" id="editModal<?= $kategoria['id_kategorii'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editModalLabel">Edytuj kategorię</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST">
                                            <input type="hidden" name="id_kategorii" value="<?= $kategoria['id_kategorii'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="nazwa" class="form-label">Nazwa</label>
                                                <input type="text" name="nazwa" class="form-control" value="<?= htmlspecialchars($kategoria['nazwa']) ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="opis" class="form-label">Opis</label>
                                                <textarea name="opis" class="form-control" required><?= htmlspecialchars($kategoria['opis']) ?></textarea>
                                            </div>

                                            <button type="submit" name="edytuj_kategorie" class="btn btn-primary" onclick="return confirmEdit()">Zaktualizuj kategorię</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="?page=kategorie&usun=<?= $kategoria['id_kategorii'] ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">Usuń</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmDelete() {
    return confirm("Czy na pewno chcesz usunąć tę kategorię?");
}
function confirmEdit() {
    return confirm("Czy na pewno chcesz zedytować tę kategorię?");
}
</script>















