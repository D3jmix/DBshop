<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $koszyk = isset($_POST['koszyk']) ? json_decode($_POST['koszyk'], true) : [];
    $imie = $_POST['imie'] ?? '';
    $nazwisko = $_POST['nazwisko'] ?? '';
    $telefon = $_POST['telefon'] ?? '';
    $email = $_POST['email'] ?? '';
    $sposob_platnosci = $_POST['sposob_platnosci'] ?? '';
    $sposob_dostawy = $_POST['sposob_dostawy'] ?? '';

    $ulica_dostawy = $_POST['ulica_dostawy'] ?? '';
    $numer_domu_dostawy = $_POST['numer_domu_dostawy'] ?? '';
    $miasto_dostawy = $_POST['miasto_dostawy'] ?? '';
    $kod_pocztowy_dostawy = $_POST['kod_pocztowy_dostawy'] ?? '';
    $kraj_dostawy = $_POST['kraj_dostawy'] ?? '';

    $ulica_rozliczeniowa = $_POST['ulica_rozliczeniowa'] ?? '';
    $numer_domu_rozliczeniowy = $_POST['numer_domu_rozliczeniowy'] ?? '';
    $miasto_rozliczeniowe = $_POST['miasto_rozliczeniowe'] ?? '';
    $kod_pocztowy_rozliczeniowy = $_POST['kod_pocztowy_rozliczeniowy'] ?? '';
    $kraj_rozliczeniowy = $_POST['kraj_rozliczeniowy'] ?? '';

    try {
    $stmt = $pdo->prepare("SELECT nazwa FROM sposob_platnosci WHERE id_platnosc = :id");
    $stmt->bindValue(':id', $sposob_platnosci, PDO::PARAM_INT);
    $stmt->execute();
    $sposob = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sposob) {
        $sposob_platnosci_nazwa = $sposob['nazwa'];
    }
    } catch (Exception $e) {
      echo "Błąd: " . $e->getMessage();
    }
} else {
    echo "<p>Niepoprawna metoda żądania. Proszę skorzystać z formularza zamówienia.</p>";
}

$zalogowany = false;
$username = '';
$sposobyPlatnosci = [];
$sposobyDostawy = [];

try {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT username FROM konta WHERE id = :id");
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $zalogowany = true;
            $username = $user['username'];

            $stmt = $pdo->prepare("SELECT * FROM sposob_platnosci");
            $stmt->execute();
            $sposobyPlatnosci = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT * FROM sposob_dostawy");
            $stmt->execute();
            $sposobyDostawy = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (Exception $e) {
    echo "Błąd: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<header class="bg-dark text-white py-3">
</header>
  <main class="container py-5">
    <h1 class="mb-4">Wybierz metodę płatności i dane do faktury</h1>    
    <form method="POST" action="platnosc_potwierdzenie.php">
      <input type="hidden" name="koszyk" id="koszyk" value='<?= htmlspecialchars(json_encode($koszyk)) ?>'>
      <input type="hidden" name="imie" id="imie" value="<?= htmlspecialchars($imie) ?>">
      <input type="hidden" name="nazwisko" id="nazwisko" value="<?= htmlspecialchars($nazwisko) ?>">
      <input type="hidden" name="telefon" id="telefon" value="<?= htmlspecialchars($telefon) ?>">
      <input type="hidden" name="email" id="email" value="<?= htmlspecialchars($email) ?>">
      <input type="hidden" name="sposob_platnosci" id="sposob_platnosci" value='<?= htmlspecialchars($sposob_platnosci) ?>'>
      <input type="hidden" name="sposob_dostawy" id="sposob_dostawy" value='<?= htmlspecialchars($sposob_dostawy) ?>'>
      <input type="hidden" name="ulica_dostawy" id="ulica_dostawy" value="<?= htmlspecialchars($ulica_dostawy) ?>">
      <input type="hidden" name="numer_domu_dostawy" id="numer_domu_dostawy" value="<?= htmlspecialchars($numer_domu_dostawy) ?>">
      <input type="hidden" name="miasto_dostawy" id="miasto_dostawy" value="<?= htmlspecialchars($miasto_dostawy) ?>">
      <input type="hidden" name="kod_pocztowy_dostawy" id="kod_pocztowy_dostawy" value="<?= htmlspecialchars($kod_pocztowy_dostawy) ?>">
      <input type="hidden" name="kraj_dostawy" id="kraj_dostawy" value="<?= htmlspecialchars($kraj_dostawy) ?>">
      <input type="hidden" name="ulica_rozliczeniowa" id="ulica_rozliczeniowa" value="<?= htmlspecialchars($ulica_rozliczeniowa) ?>">
      <input type="hidden" name="numer_domu_rozliczeniowy" id="numer_domu_rozliczeniowy" value="<?= htmlspecialchars($numer_domu_rozliczeniowy) ?>">
      <input type="hidden" name="miasto_rozliczeniowe" id="miasto_rozliczeniowe" value="<?= htmlspecialchars($miasto_rozliczeniowe) ?>">
      <input type="hidden" name="kod_pocztowy_rozliczeniowy" id="kod_pocztowy_rozliczeniowy" value="<?= htmlspecialchars($kod_pocztowy_rozliczeniowy) ?>">
      <input type="hidden" name="kraj_rozliczeniowy" id="kraj_rozliczeniowy" value="<?= htmlspecialchars($kraj_rozliczeniowy) ?>">    
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3"> 
            <div class="col-md-6">
              <?php
              if (!empty($sposob_platnosci_nazwa)) {
                if ($sposob_platnosci_nazwa == 'karta') {
                  echo '<h3>Podaj dane do karty:</h3>';
                  echo '<input type="text" class="form-control mt-3" placeholder="Numer karty" name="numer_karty" id="numer_karty" required>';
                  echo '<input type="date" class="form-control mt-3" placeholder="Data ważności" name="data_waznosci" id="data_waznosci" required>';
                  echo '<input type="text" class="form-control mt-3" placeholder="CVV" name="cvv" id="cvv" required>';
                  echo '<input type="text" class="form-control mt-3" placeholder="Nazwa posiadacza" name="nazwa_posiadacza" id="nazwa_posiadacza" required>';
                } elseif ($sposob_platnosci_nazwa == 'blik') {
                  echo '<h3>Podaj numer telefonu i kod blik:</h3>';
                  echo '<input type="text" class="form-control mt-3" placeholder="Numer telefonu" name="numer_telefonu" id="numer_telefonu" required>';
                  echo '<input type="text" class="form-control mt-3" placeholder="Kod BLIK" name="kod_blik" id="kod_blik" required>';
                } elseif ($sposob_platnosci_nazwa == 'gotówka') {
                  echo "<p>Płatność zostanie pobrana przy doręczeniu paczki.</p>";
                } elseif ($sposob_platnosci_nazwa == 'przelew') {
                  echo "<p>Dane do przelewu: Numer konta: 1234567890, Nazwa banku: XYZ Bank.</p>";
                }
              }
              ?>
            </div>
          </div>
        </div>        
        <div class="col-md-6">
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="dane_do_faktury" id="dane_do_faktury" onclick="toggleInvoiceFields()">
            <label class="form-check-label" for="dane_do_faktury">Dane do faktury</label>
          </div>          
          <div id="invoice-fields" style="display: none;">
            <input type="text" class="form-control mt-3" placeholder="Ulica" name="ulica_faktura" id="ulica_faktura">
            <input type="text" class="form-control mt-3" placeholder="Numer budynku" name="numer_budynku_faktura" id="numer_budynku_faktura">
            <input type="text" class="form-control mt-3" placeholder="Miasto" name="miasto_faktura" id="miasto_faktura">
            <input type="text" class="form-control mt-3" placeholder="Kod pocztowy" name="kod_pocztowy_faktura" id="kod_pocztowy_faktura">
            <input type="text" class="form-control mt-3" placeholder="Kraj" name="kraj_faktura" id="kraj_faktura">
            <input type="text" class="form-control mt-3" placeholder="NIP" name="nip_faktura" id="nip_faktura">
            <input type="text" class="form-control mt-3" placeholder="Nazwa firmy" name="nazwa_firmy_faktura" id="nazwa_firmy_faktura">
          </div>
        </div>
        <button type="submit" class="btn btn-success mt-3">Zatwierdź zamówienie</button>
      </div>
    </form>
  </main>

<footer class="bg-dark text-white py-4">
    <div class="container text-center">
        <p>&copy; 2024 DBShop. Wszystkie prawa zastrzeżone.</p>
    </div>
</footer>

<script>
    function toggleInvoiceFields() {
        const invoiceFields = document.getElementById('invoice-fields');
        invoiceFields.style.display = invoiceFields.style.display === 'none' ? 'block' : 'none';
    }
    function validateForm(event) {
        const numerTelefonu = document.getElementById('numer_telefonu');
        const kodBlik = document.getElementById('kod_blik');
        const numerKarty = document.getElementById('numer_karty');
        const cvv = document.getElementById('cvv');
        const nazwaPosiadacza = document.getElementById('nazwa_posiadacza');
        const dataWaznosci = document.getElementById('data_waznosci');
        const daneDoFaktury = document.getElementById('dane_do_faktury');
        const invoiceFields = [
            document.getElementById('ulica_faktura'),
            document.getElementById('numer_budynku_faktura'),
            document.getElementById('miasto_faktura'),
            document.getElementById('kod_pocztowy_faktura'),
            document.getElementById('kraj_faktura'),
            document.getElementById('nip_faktura'),
            document.getElementById('nazwa_firmy_faktura'),
        ];

        const now = new Date();
        const validationErrors = [];
      
        if (numerTelefonu && numerTelefonu.value && !/^\d{9}$/.test(numerTelefonu.value)) {
            validationErrors.push("Numer telefonu musi mieć 9 cyfr.");
        }

        if (kodBlik && kodBlik.value && !/^\d{6}$/.test(kodBlik.value)) {
            validationErrors.push("Kod BLIK musi mieć 6 cyfr.");
        }

        if (numerKarty && numerKarty.value && !/^\d{16}$/.test(numerKarty.value)) {
            validationErrors.push("Numer karty musi mieć 16 cyfr.");
        }

        if (cvv && cvv.value && !/^\d{3}$/.test(cvv.value)) {
            validationErrors.push("CVV musi mieć 3 cyfry.");
        }

        if (nazwaPosiadacza && nazwaPosiadacza.value && !/^[a-zA-Z]+\s[a-zA-Z]+$/.test(nazwaPosiadacza.value)) {
            validationErrors.push("Nazwa posiadacza musi składać się z imienia i nazwiska.");
        }

        if (dataWaznosci && dataWaznosci.value) {
            const inputDate = new Date(dataWaznosci.value);
            if (inputDate <= now) {
                validationErrors.push("Data ważności karty nie może być wcześniejsza niż obecna.");
            }
        }

        if (daneDoFaktury && daneDoFaktury.checked) {
            invoiceFields.forEach(field => {
                if (!field.value.trim()) {
                    validationErrors.push(`Pole "${field.placeholder}" musi zostać uzupełnione.`);
                }
            });

            const nipField = document.getElementById('nip_faktura');
            if (nipField && !/^\d{10}$/.test(nipField.value)) {
                validationErrors.push("NIP musi mieć 10 cyfr.");
            }

            const kodPocztowyField = document.getElementById('kod_pocztowy_faktura');
            if (kodPocztowyField && !/^\d{2}-\d{3}$/.test(kodPocztowyField.value)) {
                validationErrors.push("Kod pocztowy musi być w formacie XX-XXX.");
            }
        }

        if (validationErrors.length > 0) {
            alert("Błędy walidacji:\n" + validationErrors.join("\n"));
            event.preventDefault();
        }
    }
  
    document.querySelector('form').addEventListener('submit', validateForm);

    function toggleInvoiceFields() {
        const invoiceFields = document.getElementById('invoice-fields');
        invoiceFields.style.display = invoiceFields.style.display === 'none' ? 'block' : 'none';
    }
</script>
</body>
</html>
























