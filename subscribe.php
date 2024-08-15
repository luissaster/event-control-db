<?php
// subscribe.php

session_start();
require_once 'includes/connect.inc.php';

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_GET['event_id'];

// Obter cursos do evento
$courses_query = "SELECT * FROM courses WHERE event_id = ?";
$stmt = $conn->prepare($courses_query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$courses_result = $stmt->get_result();

// Inscrever-se em um curso
if (isset($_POST['subscribe'])) {
    $course_id = $_POST['course_id'];

    $query = "INSERT INTO registrations (user_id, course_id) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $course_id);

    try {
        $stmt->execute();
        $message = "Inscrição realizada com sucesso!";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Inscrição em Cursos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Inscrição em Cursos</h2>
    <?php if (isset($message)): ?>
        <p><?php echo $message; ?></p>
    <?php elseif (isset($error)): ?>
        <p><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- Listar cursos disponíveis para inscrição -->
    <form method="post" action="subscribe.php?event_id=<?php echo $event_id; ?>">
        <label for="course_id">Selecione o Curso:</label>
        <select id="course_id" name="course_id" required>
            <?php while ($course = $courses_result->fetch_assoc()): ?>
                <option value="<?php echo $course['id']; ?>">
                    <?php echo $course['title'] . " - " . $course['course_date'] . " " . $course['start_time']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit" name="subscribe">Inscrever-se</button>
    </form>

    <p><a href="dashboard.php">Voltar ao Dashboard</a></p>
</body>
</html>
