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

// Lógica de Registro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $matricula = $_POST['matricula'];
    $password = $_POST['password'];

    // Verificar se o email ou matrícula já existem
    $check_query = "SELECT id FROM users WHERE email = ? OR matricula = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ss", $email, $matricula);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $register_error = "Email ou matrícula já cadastrados.";
    } else {
        // Inserir novo usuário, a role será definida por padrão no banco de dados como 'student'
        $insert_query = "INSERT INTO users (name, email, matricula, password) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssss", $name, $email, $matricula, $password);

        if ($stmt->execute()) {
            $register_success = "Registro realizado com sucesso! Faça login.";
        } else {
            $register_error = "Erro ao registrar. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login e Registro</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($login_error)): ?>
        <p><?php echo $login_error; ?></p>
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

</body>
</html>
