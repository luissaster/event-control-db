<?php
// index.php

session_start();
require_once 'includes/connect.inc.php';

// Lógica de Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Consulta para verificar o usuário e sua role
    $query = "SELECT id, name, role FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $name, $role);
        $stmt->fetch();

        // Armazena as informações do usuário na sessão
        $_SESSION['user'] = $name;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role; // Armazena a role do usuário na sessão

        // Redireciona para o dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        $login_error = "Email ou senha incorretos.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login e Registro</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (isset($login_error)): ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php endif; ?>
        <form method="post" action="index.php">
            <input type="hidden" name="login" value="1">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Senha:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Entrar</button>
        </form>
        <a href="register.php">Registrar-se</a>
    </div>
</body>
</html>
