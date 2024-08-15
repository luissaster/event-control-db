<?php
// Conectar ao banco de dados
include 'includes/connect.inc.php';

// Definir o fuso horário para o Brasil (Brasília)
date_default_timezone_set('America/Sao_Paulo');

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Obter o ID do usuário logado
$user_id = $_SESSION['user_id'];

// Recuperar informações do usuário
$sql_user = "SELECT name, role, matricula FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

// Obter os cursos nos quais o usuário está inscrito
$sql_courses = "SELECT c.id, c.title, c.course_date, c.start_time, c.end_time, e.title AS event_title 
                FROM registrations r
                JOIN courses c ON r.course_id = c.id
                JOIN events e ON c.event_id = e.id
                WHERE r.user_id = ?
                ORDER BY c.course_date, c.start_time";

$stmt_courses = $conn->prepare($sql_courses);
$stmt_courses->bind_param('i', $user_id);
$stmt_courses->execute();
$result_courses = $stmt_courses->get_result();

// Obter todos os eventos disponíveis
$sql_events = "SELECT id, title, description FROM events";
$result_events = $conn->query($sql_events);

// Verificar se a consulta retornou resultados
if ($result_courses === FALSE || $result_events === FALSE) {
    die("Erro na consulta ao banco de dados: " . $conn->error);
}

// Função para se matricular em um curso
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['course_id'])) {
    $course_id = $_POST['course_id'];
    
    // Verificar se o usuário já está matriculado em outro curso no mesmo horário
    $sql_check = "SELECT COUNT(*) AS count
                  FROM registrations r
                  JOIN courses c ON r.course_id = c.id
                  WHERE r.user_id = ? AND (c.course_date = (SELECT course_date FROM courses WHERE id = ?) 
                  AND (c.start_time <= (SELECT end_time FROM courses WHERE id = ?) 
                  AND c.end_time >= (SELECT start_time FROM courses WHERE id = ?)))";
    
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('iiii', $user_id, $course_id, $course_id, $course_id);
    $stmt_check->execute();
    $check_result = $stmt_check->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo "<script>alert('Você já está matriculado em outro curso no mesmo horário.');</script>";
    } else {
        // Inserir nova matrícula
        $sql_insert = "INSERT INTO registrations (user_id, course_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param('ii', $user_id, $course_id);
        if ($stmt_insert->execute()) {
            header('Location: dashboard.php');
            exit();
        } else {
            echo "<script>alert('Erro ao realizar matrícula.');</script>";
        }
    }
}

// Função para se desmatricular de um curso
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['unregister_course_id'])) {
    $unregister_course_id = $_POST['unregister_course_id'];
    
    // Remover matrícula
    $sql_delete = "DELETE FROM registrations WHERE user_id = ? AND course_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param('ii', $user_id, $unregister_course_id);
    if ($stmt_delete->execute()) {
        header('Location: dashboard.php');
        exit();
    } else {
        echo "<script>alert('Erro ao realizar desmatrícula.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>
            <?php
            // Saudação com base na hora do dia
            $hour = date('H');
            if ($hour < 12) {
                echo "Bom Dia, " . htmlspecialchars($user['name']) . "!";
            } elseif ($hour < 18) {
                echo "Boa Tarde, " . htmlspecialchars($user['name']) . "!";
            } else {
                echo "Boa Noite, " . htmlspecialchars($user['name']) . "!";
            }
            ?>
        </h1>
        <p>Matrícula: <?php echo htmlspecialchars($user['matricula']); ?></p>
        <p>Cargo: <?php if ($user['role'] == 'admin') echo 'Administrador'; else echo 'Aluno'; ?></p>
        <nav>
            <a href="index.php">Sair</a>
            <?php if ($user['role'] == 'admin'): ?>
                <a href="manage_events.php">Gerenciar Eventos</a>
                <a href="reports.php">Relatório</a>
            <?php endif; ?>
        </nav>
        <section>
            <h2>Minha Agenda</h2>
            <table>
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Título do Curso</th>
                        <th>Data</th>
                        <th>Início</th>
                        <th>Fim</th>
                        <th>Desmatricular</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Exibir os cursos em uma tabela
                    while ($row = $result_courses->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['event_title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['course_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                        echo "<td>";
                        echo "<form method='POST' action='dashboard.php'>";
                        echo "<input type='hidden' name='unregister_course_id' value='" . htmlspecialchars($row['id']) . "'>";
                        echo "<button type='submit'>Desmatricular</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
        <section>
            <h2>Todos os Eventos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Descrição</th>
                        <th>Ver Cursos</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Exibir todos os eventos em uma tabela
                    while ($row = $result_events->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>";
                        echo "<form method='POST' action='dashboard.php'>";
                        echo "<input type='hidden' name='event_id' value='" . htmlspecialchars($row['id']) . "'>";
                        echo "<button type='submit'>Ver Cursos</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
        <?php
        // Mostrar cursos de um evento específico se selecionado
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];

            // Obter os cursos do evento selecionado
            $sql_courses_event = "SELECT c.id, c.title, c.course_date, c.start_time, c.end_time
                                  FROM courses c
                                  WHERE c.event_id = ?";
            $stmt_courses_event = $conn->prepare($sql_courses_event);
            $stmt_courses_event->bind_param('i', $event_id);
            $stmt_courses_event->execute();
            $result_courses_event = $stmt_courses_event->get_result();

            echo "<section>";
            echo "<h2>Cursos do Evento</h2>";
            echo "<table>";
            echo "<thead><tr><th>Título do Curso</th><th>Data</th><th>Início</th><th>Fim</th><th>Matricular</th></tr></thead>";
            echo "<tbody>";

            while ($row = $result_courses_event->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                echo "<td>" . htmlspecialchars($row['course_date']) . "</td>";
                echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                echo "<td>";
                echo "<form method='POST' action='dashboard.php'>";
                echo "<input type='hidden' name='course_id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<button type='submit'>Matricular</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }

            echo "</tbody></table></section>";
        }
        ?>
    </div>
</body>
</html>
