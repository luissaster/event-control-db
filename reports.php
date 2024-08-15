<?php
// reports.php

session_start();
require_once 'includes/connect.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Obter lista de usuários
$users_query = "SELECT * FROM users";
$users_result = $conn->query($users_query);

// Obter lista de eventos e cursos
$events_query = "SELECT e.title AS event_title, c.title AS course_title, c.course_date, c.start_time, c.end_time 
                 FROM events e 
                 JOIN courses c ON e.id = c.event_id";
$events_result = $conn->query($events_query);

// Obter lista de inscrições
$registrations_query = "SELECT u.name, u.matricula, c.title AS course_title, e.title AS event_title 
                        FROM registrations r 
                        JOIN users u ON r.user_id = u.id 
                        JOIN courses c ON r.course_id = c.id 
                        JOIN events e ON c.event_id = e.id";
$registrations_result = $conn->query($registrations_query);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatórios</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Relatórios</h2>

    <!-- Relatório de Usuários -->
    <h3>Usuários Cadastrados</h3>
    <ul>
        <?php while ($user = $users_result->fetch_assoc()): ?>
            <li><?php echo $user['name'] . " - " . $user['matricula']; ?></li>
        <?php endwhile; ?>
    </ul>

    <!-- Relatório de Eventos e Cursos -->
    <h3>Eventos e Cursos Cadastrados</h3>
    <ul>
        <?php while ($event = $events_result->fetch_assoc()): ?>
            <li><?php echo $event['event_title'] . " - " . $event['course_title'] . " (" . $event['course_date'] . " " . $event['start_time'] . "-" . $event['end_time'] . ")"; ?></li>
        <?php endwhile; ?>
    </ul>

    <!-- Relatório de Inscrições -->
    <h3>Inscrições</h3>
    <ul>
        <?php while ($registration = $registrations_result->fetch_assoc()): ?>
            <li><?php echo $registration['name'] . " (" . $registration['matricula'] . ") - " . $registration['course_title'] . " em " . $registration['event_title']; ?></li>
        <?php endwhile; ?>
    </ul>

    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</body>
</html>
