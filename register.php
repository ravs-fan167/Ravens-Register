<?php
include 'config.php';

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$email    = trim($_POST['email'] ?? '');

if ($username == '' || $password == '') {
    die("❌ Username dan password wajib diisi!");
}

$check = $conn->prepare("SELECT * FROM accounts WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("⚠️ Username sudah digunakan!");
}

$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO accounts (username, password, email, register_date) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("sss", $username, $hashed, $email);

if ($stmt->execute()) {
    echo "✅ Akun berhasil dibuat! Silakan login di game.";
} else {
    echo "❌ Gagal menyimpan data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>