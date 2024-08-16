<?php
// manage_events.php

session_start();
require_once 'includes/connect.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Adicionar Evento
if (isset($_POST['add_event'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "INSERT INTO events (title, description, start_date, end_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $title, $description, $start_date, $end_date);
    $stmt->execute();
}

// Adicionar Curso
if (isset($_POST['add_course'])) {
    $event_id = $_POST['event_id'];
    $title = $_POST['course_title'];
    $description = $_POST['course_description'];
    $course_date = $_POST['course_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $query = "INSERT INTO courses (event_id, title, description, course_date, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", $event_id, $title, $description, $course_date, $start_time, $end_time);
    $stmt->execute();
}

// Obter lista de eventos
$events_query = "SELECT * FROM events";
$events_result = $conn->query($events_query);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerência de Eventos e Cursos</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/manage_events.css">
</head>
<body>
    <div class="container">
        <h2>Gerência de Eventos e Cursos</h2>

        <!-- Formulário para adicionar evento -->
        <h3>Adicionar Evento</h3>
        <form method="post" action="manage_events.php">
            <div class="form-group">
                <label for="title">Título:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Descrição:</label>
                <textarea id="description" name="description"></textarea>
            </div>
            <div class="form-inline">
                <div class="form-group">
                    <label for="start_date">Data de Início:</label>
                    <input type="date" id="start_date" name="start_date" required>
                </div>
                <div class="form-group">
                    <label for="end_date">Data de Término:</label>
                    <input type="date" id="end_date" name="end_date" required>
                </div>
            </div>
            <button type="submit" name="add_event">Adicionar Evento</button>
        </form>

        <!-- Formulário para adicionar curso -->
        <h3>Adicionar Curso</h3>
        <form method="post" action="manage_events.php">
            <div class="form-group">
                <label for="event_id">Evento:</label>
                <select id="event_id" name="event_id" required>
                    <?php while ($event = $events_result->fetch_assoc()): ?>
                        <option value="<?php echo $event['id']; ?>"><?php echo $event['title']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="course_title">Título do Curso:</label>
                <input type="text" id="course_title" name="course_title" required>
            </div>
            <div class="form-group">
                <label for="course_description">Descrição:</label>
                <textarea id="course_description" name="course_description"></textarea>
            </div>
            <div class="form-inline">
                <div class="form-group">
                    <label for="course_date">Data do Curso:</label>
                    <input type="date" id="course_date" name="course_date" required>
                </div>
                <div class="form-group">
                    <label for="start_time">Hora de Início:</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="end_time">Hora de Término:</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
            </div>
            <button type="submit" name="add_course">Adicionar Curso</button>
        </form>

        <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
    </div>
</body>
</html>

