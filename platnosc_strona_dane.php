<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $koszyk = json_decode($_POST['koszyk'], true);
    $sposob_platnosci = $_POST['sposob_platnosci'];
    $sposob_dostawy = $_POST['sposob_dostawy']; 
}
   
$zalogowany = isset($_SESSION['user_id']);
$dane_uzytkownika = [];

if ($zalogowany) {
    $id_konta = $_SESSION['user_id'];
    
    $zapytanie_uzytkownik = "SELECT k.imie, k.nazwisko, k.nr_tel, u.email, k.id_klienta, k.ulica, k.numer_domu, k.kod_pocztowy, k.miasto, k.kraj FROM konta u JOIN klienci k ON u.id_klienta = k.id_klienta WHERE u.id = ?";
    $stmt_uzytkownik = $pdo->prepare($zapytanie_uzytkownik);
    $stmt_uzytkownik->execute([$id_konta]);
    $dane_uzytkownika = $stmt_uzytkownik->fetch(PDO::FETCH_ASSOC);

    if (!$dane_uzytkownika) {
        $dane_uzytkownika = [];
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz zamówienia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container py-5">
    <h1 class="text-center mb-4">Formularz Zamówienia</h1>

    <form id="formularz-zamowienia" action="platnosc_strona_finalizacja.php" method="POST">
        <input type="hidden" name="koszyk" id="koszyk" value='<?= htmlspecialchars(json_encode($koszyk)) ?>'>

        <div class="mb-4">
            <h4>Dane osobowe</h4>
            <?php if ($zalogowany): ?>
                <p><strong>Imię:</strong> <?= htmlspecialchars($dane_uzytkownika['imie']) ?></p>
                <p><strong>Nazwisko:</strong> <?= htmlspecialchars($dane_uzytkownika['nazwisko']) ?></p>
                <p><strong>Telefon:</strong> <?= htmlspecialchars($dane_uzytkownika['nr_tel']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($dane_uzytkownika['email']) ?></p>
                <input type="hidden" name="imie" value="<?= htmlspecialchars($dane_uzytkownika['imie']) ?>">
                <input type="hidden" name="nazwisko" value="<?= htmlspecialchars($dane_uzytkownika['nazwisko']) ?>">
                <input type="hidden" name="telefon" value="<?= htmlspecialchars($dane_uzytkownika['nr_tel']) ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($dane_uzytkownika['email']) ?>">
            <?php else: ?>
                <div class="mb-3">
                    <label for="imie" class="form-label">Imię</label>
                    <input type="text" id="imie" name="imie" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nazwisko" class="form-label">Nazwisko</label>
                    <input type="text" id="nazwisko" name="nazwisko" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="telefon" class="form-label">Telefon</label>
                    <input type="text" id="telefon" name="telefon" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
            <?php endif; ?>
        </div>

             <input type="hidden" name="sposob_platnosci" id="sposob_platnosci" value='<?= htmlspecialchars($sposob_platnosci) ?>'>

            <input type="hidden" name="sposob_dostawy" id="sposob_dostawy" value='<?= htmlspecialchars($sposob_dostawy) ?>'>

      <div class="mb-4">
        <?php if ($zalogowany && isset($dane_uzytkownika['ulica'])): ?>
        <div id="nowy-adres-dostawy" class="adres-form">
          <div class="mb-2">
            <label for="ulica_dostawy" class="form-label">Ulica</label>
            <input type="text" id="ulica_dostawy" name="ulica_dostawy" class="form-control" value="<?= htmlspecialchars($dane_uzytkownika['ulica']) ?>" required>
          </div>
          <div class="mb-2">
            <label for="numer_domu_dostawy" class="form-label">Numer domu</label>
            <input type="text" id="numer_domu_dostawy" name="numer_domu_dostawy" class="form-control" value="<?= htmlspecialchars($dane_uzytkownika['numer_domu']) ?>" required>
          </div>
          <div class="mb-2">
            <label for="kod_pocztowy_dostawy" class="form-label">Kod pocztowy</label>
            <input type="text" id="kod_pocztowy_dostawy" name="kod_pocztowy_dostawy" class="form-control" value="<?= htmlspecialchars($dane_uzytkownika['kod_pocztowy']) ?>" required>
          </div>
          <div class="mb-2">
            <label for="miasto_dostawy" class="form-label">Miasto</label>
            <input type="text" id="miasto_dostawy" name="miasto_dostawy" class="form-control" value="<?= htmlspecialchars($dane_uzytkownika['miasto']) ?>" required>
          </div>
          <div class="mb-2">
            <label for="kraj_dostawy" class="form-label">Kraj</label>
            <input type="text" id="kraj_dostawy" name="kraj_dostawy" class="form-control" value="<?= htmlspecialchars($dane_uzytkownika['kraj']) ?>" required>
          </div>
        </div>
        <?php else: ?>
        <div id="nowy-adres-dostawy" class="adres-form">
          <div class="mb-2">
            <label for="ulica_dostawy" class="form-label">Ulica</label>
            <input type="text" id="ulica_dostawy" name="ulica_dostawy" class="form-control" required>
          </div>
          <div class="mb-2">
            <label for="numer_domu_dostawy" class="form-label">Numer domu</label>
            <input type="text" id="numer_domu_dostawy" name="numer_domu_dostawy" class="form-control" required>
          </div>
          <div class="mb-2">
            <label for="kod_pocztowy_dostawy" class="form-label">Kod pocztowy</label>
            <input type="text" id="kod_pocztowy_dostawy" name="kod_pocztowy_dostawy" class="form-control" required>
          </div>
          <div class="mb-2">
            <label for="miasto_dostawy" class="form-label">Miasto</label>
            <input type="text" id="miasto_dostawy" name="miasto_dostawy" class="form-control" required>
          </div>
          <div class="mb-2">
            <label for="kraj_dostawy" class="form-label">Kraj</label>
            <input type="text" id="kraj_dostawy" name="kraj_dostawy" class="form-control" value="Polska" required>
          </div>
        </div>
        <?php endif; ?>
        
        <div class="mb-4">
          <h3>Adres rozliczeniowy (opcjonalny)</h3>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="adres_rozliczeniowy_check" onclick="toggleBillingAddress()">
            <label class="form-check-label" for="adres_rozliczeniowy_check">Podaj jeśli inny niż adres dostawy</label>
        </div>
          <div id="billing-address" style="display: none;">
            <input type="text" class="form-control mt-3" placeholder="Ulica" name="ulica_rozliczeniowa">
            <input type="text" class="form-control mt-3" placeholder="Numer domu" name="numer_domu_rozliczeniowy">
            <input type="text" class="form-control mt-3" placeholder="Miasto" name="miasto_rozliczeniowe">
            <input type="text" class="form-control mt-3" placeholder="Kod pocztowy" name="kod_pocztowy_rozliczeniowy">
            <input type="text" class="form-control mt-3" placeholder="Kraj" name="kraj_rozliczeniowy">
          </div>
        </div>
       </div>     

        <button type="submit" class="btn btn-primary">Przejdź dalej</button>
    </form>
</div>
<script>
function toggleBillingAddress() {
        const billingAddress = document.getElementById('billing-address');
        billingAddress.style.display = billingAddress.style.display === 'none' ? 'block' : 'none';
}
  
function validateForm(event) {
        const billingCheckbox = document.getElementById('adres_rozliczeniowy_check');
        const billingAddress = document.getElementById('billing-address');
        const billingInputs = billingAddress.querySelectorAll('input');

        const kodPocztowyDostawy = document.getElementById('kod_pocztowy_dostawy').value;
        const regexKodPocztowy = /^\d{2}-\d{3}$/;

        let valid = true;
        let errorMessage = '';

        if (!regexKodPocztowy.test(kodPocztowyDostawy)) {
            valid = false;
            errorMessage += "Kod pocztowy dostawy musi być w formacie XX-XXX (np. 01-234).\n";
        }

        if (billingCheckbox.checked) {
            billingInputs.forEach(input => {
                if (input.value.trim() === '') {
                    valid = false;
                    errorMessage += `Pole "${input.placeholder}" w adresie rozliczeniowym musi być uzupełnione.\n`;
                }
            });

            const kodPocztowyRozliczeniowy = document.querySelector('[name="kod_pocztowy_rozliczeniowy"]').value;
            if (!regexKodPocztowy.test(kodPocztowyRozliczeniowy)) {
                valid = false;
                errorMessage += "Kod pocztowy rozliczeniowy musi być w formacie XX-XXX (np. 01-234).\n";
            }
        }

        if (!valid) {
            event.preventDefault();
            alert(errorMessage);
        }
    }

    document.getElementById('formularz-zamowienia').addEventListener('submit', validateForm);

    function toggleBillingAddress() {
        const billingAddress = document.getElementById('billing-address');
        billingAddress.style.display = billingAddress.style.display === 'none' ? 'block' : 'none';
    }
</script>
</body>
</html>






