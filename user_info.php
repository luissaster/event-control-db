<?php
// Conectar ao banco de dados
include 'includes/connect.inc.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Obter o ID do usuário logado
$user_id = $_SESSION['user_id'];

// Recuperar informações do usuário
$sql_user = "SELECT name, email, role, matricula FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

// Atualizar as informações do usuário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Atualizar os dados, exceto matrícula e role
    $sql_update = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('sssi', $name, $email, $password, $user_id);

    if ($stmt_update->execute()) {
        echo "<script>alert('Informações alteradas com sucesso.');</script>";
        header('Location: user_info.php');
        exit();
    } else {
        echo "<script>alert('Erro ao atualizar informações.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informações do Usuário</title>
    <link rel="stylesheet" href="css/user_info.css">
    <script>
        function habilitarEdicao() {
            document.getElementById('name-text').style.display = 'none';
            document.getElementById('email-text').style.display = 'none';
            document.getElementById('password-text').style.display = 'none';

            document.getElementById('name').style.display = 'inline-block';
            document.getElementById('email').style.display = 'inline-block';
            document.getElementById('password').style.display = 'inline-block';

            document.getElementById('editar-btn').style.display = 'none';
            document.getElementById('confirmar-btn').style.display = 'inline-block';
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Informações do Usuário</h1>
        <form method="POST" action="user_info.php">
            <div>
                <label for="name">Nome:</label>
                <span id="name-text"><?php echo htmlspecialchars($user['name']); ?></span>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" style="display:none;">
            </div>
            <div>
                <label for="email">Email:</label>
                <span id="email-text"><?php echo htmlspecialchars($user['email']); ?></span>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="display:none;">
            </div>
            <div>
                <label for="password">Senha:</label>
                <span id="password-text">******</span>
                <input type="password" name="password" id="password" style="display:none;">
            </div>
            <div>
                <label for="role">Cargo:</label>
                <span><?php echo htmlspecialchars($user['role'] == 'admin' ? 'Administrador' : 'Aluno'); ?></span>
            </div>
            <div>
                <label for="matricula">Matrícula:</label>
                <span><?php echo htmlspecialchars($user['matricula']); ?></span>
            </div>
            <div>
                <button type="button" id="editar-btn" onclick="habilitarEdicao()">Editar Informações</button>
                <button type="submit" id="confirmar-btn" style="display:none;">Confirmar Alterações</button>
                <a href="dashboard.php"><button type="button">Voltar à Dashboard</button></a>
            </div>
        </form>
    </div>
</body>
</html>
