<?php
// register.php

require_once 'includes/connect.inc.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $matricula = $_POST['matricula'];
    $password = $_POST['password'];

    $query = "INSERT INTO users (name, email, matricula, password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $name, $email, $matricula, $password);

    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        $error = "Erro ao cadastrar. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro - Sistema de Controle de Eventos Acadêmicos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Cadastro de Usuário</h2>
    <form method="post" action="register.php">
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" required>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required>
        <label for="matricula">Matrícula:</label>
        <input type="text" id="matricula" name="matricula" required>
        <label for="password">Senha:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Cadastrar</button>
        <?php if (isset($error)): ?>
            <p><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
