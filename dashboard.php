<?php
// dashboard.php

session_start();
require_once 'includes/connect.inc.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Bem-vindo, <?php echo $_SESSION['user']; ?> - <?php echo $_SESSION['role'] == 'admin' ? 'Administrador' : 'Usuário';?></h2>

    <!-- Links disponíveis para todos os usuários -->
    <h3>Eventos Disponíveis</h3>
    <ul>
        <?php
        $events_query = "SELECT * FROM events";
        $events_result = $conn->query($events_query);

        while ($event = $events_result->fetch_assoc()): ?>
            <li>
                <a href="subscribe.php?event_id=<?php echo $event['id']; ?>">
                    <?php echo $event['title'] . " (" . $event['start_date'] . " - " . $event['end_date'] . ")"; ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>

    <!-- Links para administradores -->
    <?php if ($user_role == 'admin'): ?>
        <h3>Gerenciamento</h3>
        <ul>
            <li><a href="manage_events.php">Gerenciar Eventos e Cursos</a></li>
            <li><a href="reports.php">Gerar Relatórios</a></li>
        </ul>
    <?php endif; ?>

    <p><a href="logout.php">Logout</a></p>
</body>
</html>
