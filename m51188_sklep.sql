-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mysql.ct8.pl
-- Generation Time: Dec 06, 2024 at 05:44 AM
-- Wersja serwera: 8.0.39
-- Wersja PHP: 8.1.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `m51188_sklep`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `adres_dostawy`
--

CREATE TABLE `adres_dostawy` (
  `id_adresu_d` int NOT NULL,
  `ulica` varchar(255) DEFAULT NULL,
  `numer_domu` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `miasto` varchar(100) DEFAULT NULL,
  `kod_pocztowy` varchar(20) DEFAULT NULL,
  `kraj` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `adres_dostawy`
--

INSERT INTO `adres_dostawy` (`id_adresu_d`, `ulica`, `numer_domu`, `miasto`, `kod_pocztowy`, `kraj`) VALUES
(3, '11-listopada', '11', 'Meksyk', '12', 'Polska'),
(5, '11-listopada', '11', 'Meksyk', '12', 'Polska'),
(10, 'kreta', '112b', 'Krecia Nora', '08-111', 'Polska'),
(20, 'glowna', '112a', 'Siedlce', '08-110', 'Polska'),
(21, 'glowna', '112a', 'Siedlce', '01-110', 'Polska'),
(22, 'glowna2DB', '112a', 'Siedlce', '08-110', 'Polska');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `adres_rozliczeniowy`
--

CREATE TABLE `adres_rozliczeniowy` (
  `id_adresu_r` int NOT NULL,
  `ulica` varchar(255) DEFAULT NULL,
  `numer_domu` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `miasto` varchar(100) DEFAULT NULL,
  `kod_pocztowy` varchar(20) DEFAULT NULL,
  `kraj` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `adres_rozliczeniowy`
--

INSERT INTO `adres_rozliczeniowy` (`id_adresu_r`, `ulica`, `numer_domu`, `miasto`, `kod_pocztowy`, `kraj`) VALUES
(1, 'glowna', '112a', 'Krecia Nora', '123', 'Polska'),
(11, '11-listopada', '2a', 'Siedlce', '08-110', 'Polska'),
(12, '', '', '', '', ''),
(13, '', '', '', '08-111', 'Polska'),
(14, '', '', '', '', 'Polska'),
(15, '11-listopada2DB', '11', 'Siedlce', '08-110', 'Polska');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `adres_user`
--

CREATE TABLE `adres_user` (
  `id` int NOT NULL,
  `id_klienta` int NOT NULL,
  `id_adres_d` int DEFAULT NULL,
  `id_adres_r` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `adres_user`
--

INSERT INTO `adres_user` (`id`, `id_klienta`, `id_adres_d`, `id_adres_r`) VALUES
(2, 4, 5, NULL),
(3, 4, NULL, 1),
(8, 4, 10, NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `dane_faktura`
--

CREATE TABLE `dane_faktura` (
  `id_faktury` int NOT NULL,
  `id_klienta` int NOT NULL,
  `NIP` varchar(10) NOT NULL,
  `nazwa_firmy` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ulica` varchar(255) NOT NULL,
  `numer_budynku` varchar(50) NOT NULL,
  `miasto` varchar(255) NOT NULL,
  `kod_pocztowy` varchar(6) NOT NULL,
  `kraj` varchar(255) NOT NULL DEFAULT 'Polska'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `dane_faktura`
--

INSERT INTO `dane_faktura` (`id_faktury`, `id_klienta`, `NIP`, `nazwa_firmy`, `ulica`, `numer_budynku`, `miasto`, `kod_pocztowy`, `kraj`) VALUES
(11, 4, '1111111111', 'aaaa', 'globawna', '12A', 'Wrocław', '08-110', 'Pols'),
(12, 4, '1111111111', 'aaaa ', 'globawna2', '12A', 'Wrocław', '08-110', 'Polska');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `Kategorie`
--

CREATE TABLE `Kategorie` (
  `id_kategorii` int NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `opis` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `Kategorie`
--

INSERT INTO `Kategorie` (`id_kategorii`, `nazwa`, `opis`) VALUES
(26, 'Smartfony i akcesoria', 'Najnowsze modele!!'),
(27, 'AGD', 'Sprzęt do domu'),
(28, 'Rozrywka', 'Sprzęty rozrywkowe'),
(29, 'Sprzęt do pielęgnacji osobistej', 'Wszystko czego potrzebujesz'),
(30, 'Sprzęt biurowy', 'Sprawdzone modele');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `kategorie_k`
--

CREATE TABLE `kategorie_k` (
  `id_produktu` int NOT NULL,
  `id_kategorii` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `kategorie_k`
--

INSERT INTO `kategorie_k` (`id_produktu`, `id_kategorii`) VALUES
(35, 26),
(35, 28),
(36, 26),
(36, 28),
(38, 26),
(38, 28),
(41, 26),
(41, 28),
(43, 26),
(44, 27),
(45, 27),
(46, 27),
(47, 27),
(48, 27),
(49, 28),
(50, 28),
(51, 28),
(52, 28),
(53, 28),
(54, 29),
(55, 29),
(56, 29),
(57, 29),
(58, 29),
(59, 30),
(61, 30),
(62, 30),
(63, 30),
(64, 30);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `klienci`
--

CREATE TABLE `klienci` (
  `id_klienta` int NOT NULL,
  `imie` varchar(50) NOT NULL,
  `nazwisko` varchar(100) NOT NULL,
  `nr_tel` varchar(12) NOT NULL,
  `ulica` varchar(100) NOT NULL,
  `numer_domu` varchar(10) NOT NULL,
  `kod_pocztowy` varchar(6) NOT NULL,
  `miasto` varchar(50) NOT NULL,
  `kraj` varchar(50) DEFAULT 'Polska',
  `notatka` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `klienci`
--

INSERT INTO `klienci` (`id_klienta`, `imie`, `nazwisko`, `nr_tel`, `ulica`, `numer_domu`, `kod_pocztowy`, `miasto`, `kraj`, `notatka`) VALUES
(4, 'Damian', 'Dejmix', '111222331', 'glowna12', '112', '08-110', 'Siedlce', 'Polska', 'Ale pytać się możesz\r\n'),
(13, 'Kacper', 'Hernandez', '222333555', 'Długa', '5', '01-234', 'Kraków', 'Polska', ''),
(33, 'Kacper', 'Andrzejuk', '123456789', 'Krótka', '12B', '12-345', 'Poznań', 'Polska', ''),
(38, 'Karol', 'Hernandez', '111222333', '11-listopada1', '1112a', '08-110', 'Krecia Nora', 'Polska', '');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `konta`
--

CREATE TABLE `konta` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_polish_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_polish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_polish_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `reset_token` varchar(64) COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `id_klienta` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `konta`
--

INSERT INTO `konta` (`id`, `username`, `password`, `email`, `created_at`, `admin`, `reset_token`, `reset_token_expires`, `id_klienta`) VALUES
(8, 'dejmix', '$2y$10$86atL4g8.SjtKs4RBgarBOJ8leSuH3ubU4kpsOr5mjqt9nJHwfaay', 'u20_damianborysiewicz@zsp1.siedlce.pl', '2024-11-07 09:48:17', 1, NULL, NULL, 4),
(24, 'Kret', '$2y$10$KFz10ixqSHmiDJ5uTGE..uKVWDLslp6fd/SLN9h8NI8AstfNhoKRa', 'u20_kacperandrzejuk@zsp1.siedlce.pl', '2024-11-21 08:53:36', 0, NULL, NULL, 33),
(28, 'DejmixXD', '$2y$10$TuWbm3yUnGEWzPYCYjdtbOHuH7QV4R3iRjqcVVWQoASSg0OxYBP/K', 'damian.borysiewicz@onet.pl', '2024-11-28 21:10:17', 0, NULL, NULL, 38);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `koszyk`
--

CREATE TABLE `koszyk` (
  `id_user` int NOT NULL,
  `id_produkt` int NOT NULL,
  `ilosc` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `koszyk`
--

INSERT INTO `koszyk` (`id_user`, `id_produkt`, `ilosc`) VALUES
(24, 57, 2),
(8, 58, 1),
(8, 62, 3),
(8, 64, 1);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `listy_ulubionych`
--

CREATE TABLE `listy_ulubionych` (
  `id` int NOT NULL,
  `id_user` int NOT NULL,
  `nazwa` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `listy_ulubionych`
--

INSERT INTO `listy_ulubionych` (`id`, `id_user`, `nazwa`) VALUES
(14, 8, 'potrzebne'),
(15, 8, 'do kuchni');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `metody_platnosci`
--

CREATE TABLE `metody_platnosci` (
  `id_metody` int NOT NULL,
  `id_klienta` int NOT NULL,
  `id_platnosci` int NOT NULL,
  `numer_karty` varchar(19) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `data_waznosci` date DEFAULT NULL,
  `cvv` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `nazwa_posiadacza` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `numer_telefonu` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `kod_blik` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_polish_ci DEFAULT NULL,
  `data_dodania` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_polish_ci;

--
-- Dumping data for table `metody_platnosci`
--

INSERT INTO `metody_platnosci` (`id_metody`, `id_klienta`, `id_platnosci`, `numer_karty`, `data_waznosci`, `cvv`, `nazwa_posiadacza`, `numer_telefonu`, `kod_blik`, `data_dodania`) VALUES
(24, 4, 3, '1111222233334444', '2025-01-01', '112', 'Damian Dejmix', '', '', '2024-12-01 20:22:23'),
(26, 4, 10, '', NULL, '', '', '111222333', '', '2024-12-01 20:23:47'),
(28, 33, 3, '1111222233334444', '2026-06-01', '234', 'Kacper Andrzejuk', '', '', '2024-12-02 13:41:50');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `produkty`
--

CREATE TABLE `produkty` (
  `id` int NOT NULL,
  `nazwa` varchar(255) NOT NULL,
  `opis` text NOT NULL,
  `cena` decimal(10,2) NOT NULL,
  `stan` int NOT NULL,
  `data_dodania` timestamp NOT NULL,
  `zdjecie` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `produkty`
--

INSERT INTO `produkty` (`id`, `nazwa`, `opis`, `cena`, `stan`, `data_dodania`, `zdjecie`) VALUES
(35, 'iPhone 15 Pro', 'Najnowszy flagowy smartfon od Apple z procesorem A17 Bionic, zaawansowanymi funkcjami fotograficznymi, w tym trybem nocnym i Dynamic Island.', 5500.00, 15, '2024-11-29 15:34:28', 'uploads/6749df0496fdd_iphone.png'),
(36, 'Samsung Galaxy S23 Ultra', 'Smartfon z ekranem AMOLED 6,8\", 200 MP aparatem i wsparciem dla rysika S Pen. Idealny do fotografii i pracy kreatywnej.', 3500.00, 20, '2024-11-29 15:36:01', 'uploads/6749df618356b_Smartfon-SAMSUNG-Galaxy-S23-Ultr.jpg'),
(38, 'Google Pixel 8', 'Telefon z zaawansowanym przetwarzaniem obrazu, sztuczną inteligencją i doskonałym trybem portretowym.', 2600.00, 25, '2024-11-29 18:10:17', 'uploads/674a0389ec4ee_Smartfon-GOOGLE-Pixel-8-5G-AI-1.jpg'),
(41, 'AirPods Pro (2. generacji)', 'Słuchawki bezprzewodowe z aktywną redukcją szumów, długim czasem pracy na baterii i wysoką jakością dźwięku.', 900.00, 25, '2024-12-01 13:22:33', 'uploads/674c6319d91fd_airpods.jpg'),
(43, 'Powerbank ANKER 525 PowerCore III Sense 20000mAh 20W', 'Wydajny powerbank, który umożliwia ładowanie dwóch urządzeń jednocześnie. Idealny w podróży.', 280.00, 25, '2024-12-01 13:45:56', 'uploads/674c68948a290_Powerbank_ANKER.jpg'),
(44, 'Odkurzacz DYSON V15 Detect Absolute', 'Odkurzacz bezprzewodowy z laserowym wykrywaniem kurzu. Idealny do sprzątania mieszkań i domów.', 2800.00, 20, '2024-12-01 13:52:51', 'uploads/674c6a330acaa_Odkurzacz-DYSON.jpg'),
(45, 'Pralka WHIRLPOOL FFB 9258 SV PL 9kg 1200 obr Steam Refresh, FreshCare', 'Cicha i energooszczędna pralka z funkcją opóźnionego startu i parowym odświeżaniem ubrań.', 2100.00, 15, '2024-12-01 13:54:42', 'uploads/674c6aa2c6adf_Pralka-WHIRLPOOL.jpg'),
(46, 'Air Fryer Frytkownica beztłuszczowa PHILIPS XXL', 'Duża frytkownica na gorące powietrze umożliwiająca smażenie bez oleju. Idealna dla zdrowych posiłków.', 1200.00, 15, '2024-12-01 13:56:48', 'uploads/674c6b202982b_Frytkownica-PHILIPS-XXL.jpg'),
(47, 'Lodówka LG GSXV90MCAE Side by Side No frost InstaView(tm)', 'Lodówka z przezroczystymi drzwiami i funkcją Wi-Fi do zarządzania produktami.', 8700.00, 15, '2024-12-01 13:59:31', 'uploads/674c6bc37eae9_Lodowka-LG.jpg'),
(48, 'Kuchenka mikrofalowa SAMSUNG MC28A5135CK 900W Slim Fry Smart Sensor Czarny', 'Kuchenka mikrofalowa z funkcją pieczenia i grillowania. Świetna do małych kuchni.', 900.00, 15, '2024-12-01 14:01:23', 'uploads/674c6c33c1ae5_Kuchenka-mikrofalowa.jpg'),
(49, 'Telewizor SONY XR-65A80LAEP 65\" OLED 4K 120Hz Google TV Dolby Atmos Dolby Vision HDMI 2.1', 'Telewizor z rozdzielczością 4K i obsługą HDR10. Wyjątkowa jakość obrazu i dźwięku.', 11000.00, 15, '2024-12-01 14:05:02', 'uploads/674c6d0e02a70_Telewizor-SONY-.jpg'),
(50, 'Soundbar BOSE Smart 900 Czarny', 'Soundbar premium z obsługą Asystenta Google i Alexy. Niesamowity dźwięk w każdym pomieszczeniu.', 4800.00, 15, '2024-12-01 14:08:31', 'uploads/674c6ddf13d4e_Soundbar-BOSE.jpg'),
(51, 'Konsola SONY PlayStation 5 Pro', 'Konsola nowej generacji z ultraszybkim dyskiem SSD i grafiką w rozdzielczości 4K. Must-have dla graczy.', 3600.00, 25, '2024-12-01 14:10:11', 'uploads/674c6e4382cf1_Konsola-SONY-PlayStation-5.jpg'),
(52, 'Konsola NINTENDO Switch Oled Czerwono-niebieska', 'Konsola hybrydowa z ulepszonym ekranem OLED i długim czasem pracy na baterii. Idealna do grania solo lub z rodziną.', 1600.00, 25, '2024-12-01 14:11:16', 'uploads/674c6e8402dc8_Konsola-NINTENDO-Switch.jpg'),
(53, 'Kierownica LOGITECH G29', 'Kierownica z pedałami i obsługą wielu platform, idealna dla fanów symulatorów wyścigów.', 1200.00, 14, '2024-12-01 14:12:15', 'uploads/674c6ebfc6e3f_Kierownica-LOGITECH.jpg'),
(54, 'Szczoteczka soniczna PHILIPS Sonicare 9000 DiamondClean HX9911/88', 'Elektryczna szczoteczka do zębów z pięcioma trybami czyszczenia i etui z ładowaniem USB.', 970.00, 15, '2024-12-01 14:28:13', 'uploads/674c727d26c61_Szczoteczka-soniczna.jpg'),
(55, 'Suszarka DYSON Supersonic Origin 1600W', 'Superszybka suszarka z inteligentnym sterowaniem temperaturą. Chroni włosy przed uszkodzeniami.', 1750.00, 15, '2024-12-01 14:29:28', 'uploads/674c72c8bea37_Suszarka-DYSON.jpg'),
(56, 'Golarka BRAUN Seria 9 Pro+ 9567CC', 'Elektryczna golarka z funkcją automatycznego czyszczenia i wygodnym uchwytem.', 1700.00, 15, '2024-12-01 14:31:23', 'uploads/674c733b3c923_Golarka-BRAUN-Seria-9.jpg'),
(57, 'Foreo Luna 4', 'Urządzenie do oczyszczania skóry twarzy i masażu, zapewniające promienny wygląd.', 900.00, 15, '2024-12-01 14:40:45', 'uploads/674c756d56447_luna foreva.jpg'),
(58, 'Depilator PANASONIC ES-EY90-A503', 'Wielofunkcyjny depilator z opcją golenia na mokro i sucho', 700.00, 13, '2024-12-01 14:42:41', 'uploads/674c75e1635db_Depilator-PANASONIC.jpg'),
(59, 'Urządzenie wielofunkcyjne HP OfficeJet Pro 8122e', 'Wszechstronna drukarka z funkcją skanowania, kopiowania i drukowania w kolorze.', 500.00, 20, '2024-12-01 15:05:10', 'uploads/674c7b26e997a_drukarka-HP.jpg'),
(61, 'Monitor DELL UltraSharp U3223QE', 'Profesjonalny monitor o wysokiej rozdzielczości, idealny do pracy z grafiką i wideo.', 3300.00, 15, '2024-12-01 15:07:47', 'uploads/674c7bc39468f_Monitor-DELL-UltraSharp-U3223QE-31.5-3840x2160px-IPS-front.jpg'),
(62, 'Klawiatura LOGITECH MX Keys Mini', 'Bezprzewodowa klawiatura o cichej pracy i podświetleniu. Ergonomiczny design.', 389.99, 18, '2024-12-01 15:09:02', 'uploads/674c7c0e656c0_Klawiatura-LOGITECH.jpg'),
(63, 'Laptop MICROSOFT Surface Laptop 5', 'Lekki laptop z doskonałą jakością ekranu i systemem Windows 11.\r\nParametry:\r\n· Waga [kg]: 1.29\r\n· Wysokość [cm]: 1.45\r\n· Szerokość [cm]: 30.8\r\n· Procesor: Intel Core i5-1235U\r\n· Generacja procesora Intel Core: 12gen\r\n· Liczba rdzeni: 10\r\n· Liczba wątków: 12\r\n· Pamięć podręczna: 12MB Cache\r\n· Maksymalna częstotliwość taktowania procesora [GHz]: 4.4 (Turbo)\r\n· Zintegrowany układ graficzny: Intel Iris Xe Graphics\r\n· Wielkość pamięci RAM [GB]: 16\r\n· Typ pamięci RAM: LPDDR5X\r\n· Częstotliwość pamięci RAM [MHz]: 5200\r\n· Maksymalna obsługiwana ilość pamięci RAM: Brak możliwości rozszerzenia\r\n· Ogólna liczba gniazd pamięci RAM: 0\r\n· Zajęte sloty na pamięć RAM: 1x 16GB (wlutowane)\r\n· Wolne sloty na pamięć RAM: 0\r\n· Pojemność dysku SSD [GB]: 512\r\n· Typ dysku SSD: PCIe NVMe\r\n· Przekątna ekranu [cal]:13.5\r\n· Rozdzielczość ekranu: 2256 x 1504\r\n· Rodzaj matrycy: Błyszcząca\r\n· Typ matrycy: PixelSense\r\n· Ekran dotykowy: Tak\r\n· Wi-Fi: Tak (Wi-Fi 6 (802.11 a/b/g/n/ac/ax))\r\n· Bluetooth: Tak (Moduł Bluetooth 5.1)\r\n\r\nZłącza:\r\n· 1 x Surface Connect\r\n· 1 x USB 3.2 Gen. 1\r\n· 1 x USB Type-C (z Thunderbolt 4)\r\n· 1 x Wyjście słuchawkowe/wejście mikrofonowe\r\n\r\n· Kod producenta: R8N-00009\r\n· Nazwa producenta/importera: MICROSOFT', 6349.99, 20, '2024-12-01 15:17:44', 'uploads/674c7e18e3f62_Laptop-MICROSOFT.jpg'),
(64, 'Kamera internetowa LOGITECH HD Pro C920 960-001055 USB-A - 1080p', 'Kamera internetowa z wysoką jakością obrazu. Idealna do wideokonferencji.\r\nParametry:\r\n· Typ sensora: CMOS\r\n· Rozdzielczość: 1920 x 1080\r\n· Rozdzielczość 4K: Nie\r\n· Kompresja wideo: H.264\r\n· Focus: Nie\r\n· Funkcja aparatu cyfrowego: Tak\r\n· Kamera: 1920 x 1080 px\r\n· Interfejs: USB\r\n· Zasilanie: USB\r\n· Mikrofon wbudowany: Tak\r\n· Funkcja wideokonferencji: Tak\r\n· Kolor: Czarny\r\n· Wyposażenie: Kabel USB\r\n\r\n· Nazwa producenta/importera: LOGITECH', 389.99, 25, '2024-12-01 15:26:25', 'uploads/674c80217b2ac_Kamera-internetowa-LOGITECH.jpg');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `sposob_dostawy`
--

CREATE TABLE `sposob_dostawy` (
  `id_dostawy` int NOT NULL,
  `nazwa` varchar(100) NOT NULL,
  `koszt` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `sposob_dostawy`
--

INSERT INTO `sposob_dostawy` (`id_dostawy`, `nazwa`, `koszt`) VALUES
(2, 'kurier', 12.00),
(5, 'paczkomat', 15.00),
(6, 'odbiór osobisty w sklepie', 0.00);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `sposob_platnosci`
--

CREATE TABLE `sposob_platnosci` (
  `id_platnosc` int NOT NULL,
  `nazwa` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `sposob_platnosci`
--

INSERT INTO `sposob_platnosci` (`id_platnosc`, `nazwa`) VALUES
(3, 'karta'),
(5, 'gotówka'),
(6, 'przelew'),
(10, 'blik');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ulubione`
--

CREATE TABLE `ulubione` (
  `id_user` int NOT NULL,
  `id_produkt` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `ulubione`
--

INSERT INTO `ulubione` (`id_user`, `id_produkt`) VALUES
(8, 62),
(8, 48),
(8, 47),
(24, 62),
(24, 45),
(8, 64);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ulubione_list_dod`
--

CREATE TABLE `ulubione_list_dod` (
  `id_produkt` int NOT NULL,
  `id_lista` int NOT NULL,
  `id_user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `ulubione_list_dod`
--

INSERT INTO `ulubione_list_dod` (`id_produkt`, `id_lista`, `id_user`) VALUES
(62, 14, 8),
(64, 14, 8),
(48, 15, 8),
(47, 15, 8);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienia`
--

CREATE TABLE `zamowienia` (
  `id_zamowienia` int NOT NULL,
  `id_klienta` int NOT NULL,
  `cena_calkowita` decimal(10,2) NOT NULL,
  `id_adres_dostawy` int NOT NULL,
  `id_adres_rozliczeniowy` int NOT NULL,
  `sposob_dostawy` int NOT NULL,
  `metoda_platnosci` int DEFAULT NULL,
  `szczegoly_platnosci` int DEFAULT NULL,
  `id_faktury` int DEFAULT NULL,
  `email_zamowienie` varchar(255) NOT NULL,
  `status` enum('oczekujace','zrealizowane','anulowane') DEFAULT 'oczekujace',
  `data_zamowienia` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `data_aktualizacji` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `zamowienia`
--

INSERT INTO `zamowienia` (`id_zamowienia`, `id_klienta`, `cena_calkowita`, `id_adres_dostawy`, `id_adres_rozliczeniowy`, `sposob_dostawy`, `metoda_platnosci`, `szczegoly_platnosci`, `id_faktury`, `email_zamowienie`, `status`, `data_zamowienia`, `data_aktualizacji`) VALUES
(101, 4, 712.00, 20, 14, 2, NULL, 6, NULL, 'u20_damianborysiewicz@zsp1.siedlce.pl', 'oczekujace', '2024-12-04 22:51:05', '2024-12-04 22:51:05'),
(102, 4, 712.00, 20, 14, 2, NULL, 6, NULL, 'u20_damianborysiewicz@zsp1.siedlce.pl', 'oczekujace', '2024-12-04 23:04:17', '2024-12-04 23:04:17'),
(104, 4, 712.00, 20, 14, 2, NULL, 6, 11, 'u20_damianborysiewicz@zsp1.siedlce.pl', 'oczekujace', '2024-12-04 23:19:03', '2024-12-04 23:19:03'),
(106, 4, 2289.99, 20, 14, 6, NULL, 5, NULL, 'u20_damianborysiewicz@zsp1.siedlce.pl', 'oczekujace', '2024-12-05 03:28:02', '2024-12-05 03:28:02'),
(108, 4, 2289.99, 20, 14, 6, NULL, 6, NULL, 'u20_damianborysiewicz@zsp1.siedlce.pl', 'anulowane', '2024-12-05 22:41:04', '2024-12-06 03:17:48');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zamowienie_produkty`
--

CREATE TABLE `zamowienie_produkty` (
  `id` int NOT NULL,
  `id_zamowienia` int DEFAULT NULL,
  `id_produktu` int NOT NULL,
  `ilosc` int NOT NULL,
  `cena_jednostkowa` decimal(10,2) NOT NULL,
  `wartosc_calkowita` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `zamowienie_produkty`
--

INSERT INTO `zamowienie_produkty` (`id`, `id_zamowienia`, `id_produktu`, `ilosc`, `cena_jednostkowa`, `wartosc_calkowita`) VALUES
(88, 101, 58, 1, 700.00, 712.00),
(89, 102, 58, 1, 700.00, 712.00),
(91, 104, 58, 1, 700.00, 712.00),
(93, 106, 58, 1, 700.00, 700.00),
(94, 106, 62, 1, 389.99, 389.99),
(95, 106, 53, 1, 1200.00, 1200.00),
(97, 108, 58, 1, 700.00, 700.00),
(98, 108, 62, 1, 389.99, 389.99),
(99, 108, 53, 1, 1200.00, 1200.00);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `adres_dostawy`
--
ALTER TABLE `adres_dostawy`
  ADD PRIMARY KEY (`id_adresu_d`);

--
-- Indeksy dla tabeli `adres_rozliczeniowy`
--
ALTER TABLE `adres_rozliczeniowy`
  ADD PRIMARY KEY (`id_adresu_r`);

--
-- Indeksy dla tabeli `adres_user`
--
ALTER TABLE `adres_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `adres_user_ibfk_1` (`id_klienta`),
  ADD KEY `adres_user_ibfk_2` (`id_adres_d`),
  ADD KEY `adres_user_ibfk_3` (`id_adres_r`);

--
-- Indeksy dla tabeli `dane_faktura`
--
ALTER TABLE `dane_faktura`
  ADD PRIMARY KEY (`id_faktury`);

--
-- Indeksy dla tabeli `Kategorie`
--
ALTER TABLE `Kategorie`
  ADD PRIMARY KEY (`id_kategorii`);

--
-- Indeksy dla tabeli `kategorie_k`
--
ALTER TABLE `kategorie_k`
  ADD KEY `id_kategorii` (`id_kategorii`),
  ADD KEY `id_produktu` (`id_produktu`);

--
-- Indeksy dla tabeli `klienci`
--
ALTER TABLE `klienci`
  ADD PRIMARY KEY (`id_klienta`);

--
-- Indeksy dla tabeli `konta`
--
ALTER TABLE `konta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_klienta` (`id_klienta`);

--
-- Indeksy dla tabeli `koszyk`
--
ALTER TABLE `koszyk`
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produkt` (`id_produkt`);

--
-- Indeksy dla tabeli `listy_ulubionych`
--
ALTER TABLE `listy_ulubionych`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeksy dla tabeli `metody_platnosci`
--
ALTER TABLE `metody_platnosci`
  ADD PRIMARY KEY (`id_metody`),
  ADD KEY `id_uzytkownika` (`id_klienta`),
  ADD KEY `id_platnosci` (`id_platnosci`);

--
-- Indeksy dla tabeli `produkty`
--
ALTER TABLE `produkty`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `sposob_dostawy`
--
ALTER TABLE `sposob_dostawy`
  ADD PRIMARY KEY (`id_dostawy`);

--
-- Indeksy dla tabeli `sposob_platnosci`
--
ALTER TABLE `sposob_platnosci`
  ADD PRIMARY KEY (`id_platnosc`);

--
-- Indeksy dla tabeli `ulubione`
--
ALTER TABLE `ulubione`
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produkt` (`id_produkt`);

--
-- Indeksy dla tabeli `ulubione_list_dod`
--
ALTER TABLE `ulubione_list_dod`
  ADD KEY `id_lista` (`id_lista`),
  ADD KEY `id_produkt` (`id_produkt`),
  ADD KEY `ulubione_list_dod_ibfk_3` (`id_user`);

--
-- Indeksy dla tabeli `zamowienia`
--
ALTER TABLE `zamowienia`
  ADD PRIMARY KEY (`id_zamowienia`),
  ADD KEY `id_klienta` (`id_klienta`),
  ADD KEY `zamowienia_ibfk_2` (`id_adres_dostawy`),
  ADD KEY `id_adres_rozliczeniowy` (`id_adres_rozliczeniowy`),
  ADD KEY `metoda_platnosci` (`metoda_platnosci`),
  ADD KEY `id_faktury` (`id_faktury`),
  ADD KEY `zamowienia_ibfk_3` (`sposob_dostawy`),
  ADD KEY `zamowienia_ibfk_4` (`szczegoly_platnosci`);

--
-- Indeksy dla tabeli `zamowienie_produkty`
--
ALTER TABLE `zamowienie_produkty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `zamowienie_produkty_ibfk_2` (`id_produktu`),
  ADD KEY `zamowienie_produkty_ibfk_1` (`id_zamowienia`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adres_dostawy`
--
ALTER TABLE `adres_dostawy`
  MODIFY `id_adresu_d` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `adres_rozliczeniowy`
--
ALTER TABLE `adres_rozliczeniowy`
  MODIFY `id_adresu_r` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `adres_user`
--
ALTER TABLE `adres_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `dane_faktura`
--
ALTER TABLE `dane_faktura`
  MODIFY `id_faktury` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `Kategorie`
--
ALTER TABLE `Kategorie`
  MODIFY `id_kategorii` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `klienci`
--
ALTER TABLE `klienci`
  MODIFY `id_klienta` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `konta`
--
ALTER TABLE `konta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `listy_ulubionych`
--
ALTER TABLE `listy_ulubionych`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `metody_platnosci`
--
ALTER TABLE `metody_platnosci`
  MODIFY `id_metody` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `produkty`
--
ALTER TABLE `produkty`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `sposob_dostawy`
--
ALTER TABLE `sposob_dostawy`
  MODIFY `id_dostawy` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sposob_platnosci`
--
ALTER TABLE `sposob_platnosci`
  MODIFY `id_platnosc` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `zamowienia`
--
ALTER TABLE `zamowienia`
  MODIFY `id_zamowienia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `zamowienie_produkty`
--
ALTER TABLE `zamowienie_produkty`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adres_user`
--
ALTER TABLE `adres_user`
  ADD CONSTRAINT `adres_user_ibfk_1` FOREIGN KEY (`id_klienta`) REFERENCES `klienci` (`id_klienta`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `adres_user_ibfk_2` FOREIGN KEY (`id_adres_d`) REFERENCES `adres_dostawy` (`id_adresu_d`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `adres_user_ibfk_3` FOREIGN KEY (`id_adres_r`) REFERENCES `adres_rozliczeniowy` (`id_adresu_r`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kategorie_k`
--
ALTER TABLE `kategorie_k`
  ADD CONSTRAINT `kategorie_k_ibfk_1` FOREIGN KEY (`id_kategorii`) REFERENCES `Kategorie` (`id_kategorii`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `kategorie_k_ibfk_2` FOREIGN KEY (`id_produktu`) REFERENCES `produkty` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `konta`
--
ALTER TABLE `konta`
  ADD CONSTRAINT `konta_ibfk_1` FOREIGN KEY (`id_klienta`) REFERENCES `klienci` (`id_klienta`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `koszyk`
--
ALTER TABLE `koszyk`
  ADD CONSTRAINT `koszyk_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `konta` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `koszyk_ibfk_3` FOREIGN KEY (`id_produkt`) REFERENCES `produkty` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `listy_ulubionych`
--
ALTER TABLE `listy_ulubionych`
  ADD CONSTRAINT `listy_ulubionych_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `konta` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `metody_platnosci`
--
ALTER TABLE `metody_platnosci`
  ADD CONSTRAINT `metody_platnosci_ibfk_2` FOREIGN KEY (`id_platnosci`) REFERENCES `sposob_platnosci` (`id_platnosc`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `metody_platnosci_ibfk_3` FOREIGN KEY (`id_klienta`) REFERENCES `klienci` (`id_klienta`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `ulubione`
--
ALTER TABLE `ulubione`
  ADD CONSTRAINT `ulubione_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `konta` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ulubione_ibfk_3` FOREIGN KEY (`id_produkt`) REFERENCES `produkty` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `ulubione_list_dod`
--
ALTER TABLE `ulubione_list_dod`
  ADD CONSTRAINT `ulubione_list_dod_ibfk_1` FOREIGN KEY (`id_lista`) REFERENCES `listy_ulubionych` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ulubione_list_dod_ibfk_2` FOREIGN KEY (`id_produkt`) REFERENCES `ulubione` (`id_produkt`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `ulubione_list_dod_ibfk_3` FOREIGN KEY (`id_user`) REFERENCES `listy_ulubionych` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `zamowienia`
--
ALTER TABLE `zamowienia`
  ADD CONSTRAINT `zamowienia_ibfk_1` FOREIGN KEY (`id_klienta`) REFERENCES `klienci` (`id_klienta`),
  ADD CONSTRAINT `zamowienia_ibfk_2` FOREIGN KEY (`id_adres_dostawy`) REFERENCES `adres_dostawy` (`id_adresu_d`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `zamowienia_ibfk_3` FOREIGN KEY (`sposob_dostawy`) REFERENCES `sposob_dostawy` (`id_dostawy`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `zamowienia_ibfk_4` FOREIGN KEY (`szczegoly_platnosci`) REFERENCES `sposob_platnosci` (`id_platnosc`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `zamowienia_ibfk_5` FOREIGN KEY (`id_adres_rozliczeniowy`) REFERENCES `adres_rozliczeniowy` (`id_adresu_r`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `zamowienia_ibfk_6` FOREIGN KEY (`metoda_platnosci`) REFERENCES `metody_platnosci` (`id_metody`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `zamowienia_ibfk_7` FOREIGN KEY (`id_faktury`) REFERENCES `dane_faktura` (`id_faktury`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `zamowienie_produkty`
--
ALTER TABLE `zamowienie_produkty`
  ADD CONSTRAINT `zamowienie_produkty_ibfk_1` FOREIGN KEY (`id_zamowienia`) REFERENCES `zamowienia` (`id_zamowienia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `zamowienie_produkty_ibfk_2` FOREIGN KEY (`id_produktu`) REFERENCES `produkty` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
