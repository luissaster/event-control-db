<?php
// edit_events.php

session_start();
require_once 'includes/connect.inc.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Atualizar Evento
if (isset($_POST['edit_event'])) {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "UPDATE events SET title = ?, description = ?, start_date = ?, end_date = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $title, $description, $start_date, $end_date, $event_id);
    $stmt->execute();
}

// Atualizar Curso
if (isset($_POST['edit_course'])) {
    $course_id = $_POST['course_id'];
    $event_id = $_POST['event_id'];
    $title = $_POST['course_title'];
    $description = $_POST['course_description'];
    $course_date = $_POST['course_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $query = "UPDATE courses SET event_id = ?, title = ?, description = ?, course_date = ?, start_time = ?, end_time = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssi", $event_id, $title, $description, $course_date, $start_time, $end_time, $course_id);
    $stmt->execute();
}

// Obter lista de eventos
$events_query = "SELECT * FROM events";
$events_result = $conn->query($events_query);

$selected_event_id = isset($_POST['event_id']) ? $_POST['event_id'] : null;
$selected_course_id = isset($_POST['course_id']) ? $_POST['course_id'] : null;

if ($selected_event_id) {
    // Obter lista de cursos para o evento selecionado
    $courses_query = "SELECT * FROM courses WHERE event_id = ?";
    $stmt = $conn->prepare($courses_query);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $courses_result = $stmt->get_result();

    // Obter detalhes do evento selecionado
    $event_query = "SELECT * FROM events WHERE id = ?";
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $event_result = $stmt->get_result()->fetch_assoc();

    // Obter detalhes do curso selecionado
    if ($selected_course_id) {
        $course_query = "SELECT * FROM courses WHERE id = ?";
        $stmt = $conn->prepare($course_query);
        $stmt->bind_param("i", $selected_course_id);
        $stmt->execute();
        $course_result = $stmt->get_result()->fetch_assoc();
    }
}

// Excluir evento
if (isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];

    $query = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    header("Location: edit_events.php");
    exit();
}

// Excluir curso
if (isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];

    $query = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();

    header("Location: edit_events.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerência de Eventos e Cursos</title>
    <link rel="stylesheet" href="css/manage_events.css">
    <style>
        /* Estilos adicionais */
        .container {
            display: flex;
            justify-content: space-between;
            width: 90%;
            /* Largura do container */
            max-width: 1200px;
            /* Largura máxima do container */
            margin: 20px auto;
            /* Centraliza o container na tela */
        }

        .left-panel {
            width: 30%;
            /* Largura do painel de eventos */
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            /* Ajusta a largura do painel ao tamanho dos botões */
        }

        .right-panel {
            width: 65%;
            /* Largura do painel de detalhes e cursos */
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-inline {
            display: flex;
            gap: 10px;
        }

        .form-inline .form-group {
            flex: 1;
        }

        button {
            margin-top: 10px;
        }

        .courses-list .form-group {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
        }

        /* Ajusta o tamanho do botão para preencher toda a largura disponível */
        .left-panel button {
            width: 100%;
            text-align: left;
            margin-bottom: 10px;
            padding: 10px;
            box-sizing: border-box;
            /* Inclui padding e border no cálculo da largura */
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Painel esquerdo: Eventos -->
        <div class="left-panel">
            <h2>Eventos</h2>
            <form method="post" action="edit_events.php">
                <?php while ($event = $events_result->fetch_assoc()): ?>
                    <button type="submit" name="event_id" value="<?php echo $event['id']; ?>"
                        style="width: 100%; text-align: left;">
                        <?php echo $event['title']; ?>
                    </button>
                <?php endwhile; ?>
            </form>
            <?php
            echo "<a href='dashboard.php'><button class='voltar'>Voltar ao Dashboard</button></a>";
            ?>
        </div>

        <!-- Painel direito: Detalhes e Cursos -->
        <div class="right-panel">
            <?php if ($selected_event_id && isset($event_result)): ?>
                <!-- Formulário de Edição de Evento -->
                <h2>Editar Evento</h2>
                <form method="post" action="edit_events.php">
                    <input type="hidden" name="event_id" value="<?php echo $selected_event_id; ?>">
                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" id="title" name="title" value="<?php echo $event_result['title']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Descrição:</label>
                        <textarea id="description" name="description"><?php echo $event_result['description']; ?></textarea>
                    </div>
                    <div class="form-inline">
                        <div class="form-group">
                            <label for="start_date">Data de Início:</label>
                            <input type="date" id="start_date" name="start_date"
                                value="<?php echo $event_result['start_date']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">Data de Término:</label>
                            <input type="date" id="end_date" name="end_date"
                                value="<?php echo $event_result['end_date']; ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="edit_event">Salvar Alterações</button>
                    <button type="submit" name="delete_event"
                        onclick="return confirm('Tem certeza que deseja deletar este evento e todos os cursos associados?')">Deletar
                        Evento</button>
                </form>

                <!-- Cursos associados ao evento -->
                <h2>Cursos Associados</h2>
                <form method="post" action="edit_events.php">
                    <input type="hidden" name="event_id" value="<?php echo $selected_event_id; ?>">
                    <div class="courses-list">
                        <?php if ($courses_result->num_rows > 0): ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <div class="form-group">
                                    <form method="post" action="edit_events.php" style="margin-bottom: 20px;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                        <div class="form-group">
                                            <label for="course_title_<?php echo $course['id']; ?>">Título do Curso:</label>
                                            <input type="text" id="course_title_<?php echo $course['id']; ?>" name="course_title"
                                                value="<?php echo $course['title']; ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="course_description_<?php echo $course['id']; ?>">Descrição:</label>
                                            <textarea id="course_description_<?php echo $course['id']; ?>"
                                                name="course_description"><?php echo $course['description']; ?></textarea>
                                        </div>
                                        <div class="form-inline">
                                            <div class="form-group">
                                                <label for="course_date_<?php echo $course['id']; ?>">Data do Curso:</label>
                                                <input type="date" id="course_date_<?php echo $course['id']; ?>" name="course_date"
                                                    value="<?php echo $course['course_date']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="start_time_<?php echo $course['id']; ?>">Hora de Início:</label>
                                                <input type="time" id="start_time_<?php echo $course['id']; ?>" name="start_time"
                                                    value="<?php echo $course['start_time']; ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="end_time_<?php echo $course['id']; ?>">Hora de Término:</label>
                                                <input type="time" id="end_time_<?php echo $course['id']; ?>" name="end_time"
                                                    value="<?php echo $course['end_time']; ?>" required>
                                            </div>
                                        </div>
                                        <button type="submit" name="edit_course">Salvar Alterações</button>
                                        <button type="submit" name="delete_course"
                                            onclick="return confirm('Tem certeza que deseja deletar este curso?')">Deletar
                                            Curso</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Não há cursos associados a este evento.</p>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>