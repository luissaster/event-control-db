<?php
// index.php

session_start();
require_once 'includes/connect.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricula = $_POST['matricula'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE matricula = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $matricula, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        // Passar as informações do login pro dashboard.php

        $_SESSION['user'] = $matricula;
        $_SESSION['role'] = $role;

        header("Location: dashboard.php");
    } else {
        $error = "Matrícula ou senha incorreta.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Controle de Eventos Acadêmicos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Login</h2>
    <form method="post" action="index.php">
        <label for="matricula">Matrícula:</label>
        <input type="text" id="matricula" name="matricula" required>
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Entrar</button>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
    <p>Não tem uma conta? <a href="register.php">Cadastre-se aqui</a></p>
</body>
</html>
