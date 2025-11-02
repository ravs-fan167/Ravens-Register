<?php
include 'config.php';

function getRandomInt($min, $max) {
    return rand($min, $max);
}

$ucp = trim($_POST['ucp'] ?? '');
$action = $_POST['action'] ?? '';

if ($ucp == '') die("❌ Masukkan nama UCP terlebih dahulu!");

if ($action === 'create') {
    // Cek format nama
    if (preg_match('/[_\d\'\W]/', $ucp)) die("⚠️ Nama UCP tidak boleh mengandung angka, simbol, atau underscore!");
    
    // Cek apakah sudah ada
    $check = $conn->prepare("SELECT * FROM ucp WHERE username = ?");
    $check->bind_param("s", $ucp);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        echo "⚠️ Nama UCP sudah digunakan! Silakan gunakan menu 'Cek Verify Code'.";
        exit;
    }

    // Generate kode verifikasi
    $pin = getRandomInt(100000, 999999);
    $verifycode = "RV-" . $pin;
    $registeredAt = date('Y-m-d H:i:s');

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO ucp (username, verifycode, registerdate) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $ucp, $verifycode, $registeredAt);
    $stmt->execute();

    echo "<div class='p-4 bg-black/40 rounded-lg'>
            <h2 class='text-xl font-bold text-sky-400'>✅ UCP Berhasil Dibuat!</h2>
            <p>Nama UCP: <strong>{$ucp}</strong></p>
            <p>Kode Verifikasi:</p>
            <div class='text-2xl font-bold text-green-400 my-2'>{$verifycode}</div>
            <p>Tanggal Buat: {$registeredAt}</p>
          </div>";
}
elseif ($action === 'check') {
    // Cek kode verifikasi dari UCP
    $check = $conn->prepare("SELECT verifycode, registerdate FROM ucp WHERE username = ?");
    $check->bind_param("s", $ucp);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        echo "❌ UCP tidak ditemukan!";
        exit;
    }

    $data = $result->fetch_assoc();
    echo "<div class='p-4 bg-black/40 rounded-lg'>
            <h2 class='text-xl font-bold text-sky-400'>🔎 Verify Code Ditemukan!</h2>
            <p>Nama UCP: <strong>{$ucp}</strong></p>
            <p>Kode Verifikasi:</p>
            <div class='text-2xl font-bold text-yellow-400 my-2'>{$data['verifycode']}</div>
            <p>Tanggal Buat: {$data['registerdate']}</p>
          </div>";
}
else {
    echo "❌ Aksi tidak valid!";
}
?>
