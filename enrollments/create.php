<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

function generateEnrollmentNumber() {
    $prefix = 'PMB';
    $suffix = 'PA';
    $randomDigits = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    return $prefix . '-' . $randomDigits . '-' . $suffix;
}

try {
    // Get students for dropdown
    $stmt = $pdo->query("SELECT id, name FROM students ORDER BY name");
    $students = $stmt->fetchAll();

    // Get courses for dropdown
    $stmt = $pdo->query("SELECT id, name FROM courses WHERE status = 'active' ORDER BY name");
    $courses = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        // Generate enrollment number and check if it's unique
        do {
            $enrollment_number = generateEnrollmentNumber();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE enrollment_number = ?");
            $stmt->execute([$enrollment_number]);
            $exists = $stmt->fetchColumn() > 0;
        } while ($exists);

        $stmt = $pdo->prepare("INSERT INTO enrollments (enrollment_number, student_id, course_id, enrollment_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $enrollment_number,
            $_POST['student_id'],
            $_POST['course_id'],
            $_POST['enrollment_date'],
            $_POST['status']
        ]);
        
        $_SESSION['success_message'] = "Matrícula criada com sucesso! Número: " . $enrollment_number;
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao criar matrícula: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Matrícula - Sistema de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h4>Sistema de Cursos</h4>
            <small>Bem-vindo, <?php echo htmlspecialchars($_SESSION["username"]); ?></small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="../dashboard.php">
                    <i class='bx bx-home'></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../courses/">
                    <i class='bx bx-book'></i> Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../students/">
                    <i class='bx bx-user'></i> Alunos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="../enrollments/">
                    <i class='bx bx-list-check'></i> Matrículas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../certificates/">
                    <i class='bx bx-certification'></i> Certificados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../users/">
                    <i class='bx bx-group'></i> Usuários
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="../logout.php">
                    <i class='bx bx-log-out'></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Nova Matrícula</h2>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Número de Matrícula</label>
                            <input type="text" class="form-control bg-light" value="<?php echo generateEnrollmentNumber(); ?>" readonly disabled>
                            <small class="text-muted">O número de matrícula será gerado automaticamente ao salvar</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Aluno</label>
                            <select class="form-control" name="student_id" required>
                                <option value="">Selecione um aluno</option>
                                <?php foreach($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Curso</label>
                            <select class="form-control" name="course_id" required>
                                <option value="">Selecione um curso</option>
                                <?php foreach($courses as $course): ?>
                                    <option value="<?php echo $course['id']; ?>">
                                        <?php echo htmlspecialchars($course['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data da Matrícula</label>
                            <input type="date" class="form-control" name="enrollment_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="active">Ativo</option>
                                <option value="completed">Concluído</option>
                                <option value="cancelled">Cancelado</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Criar Matrícula</button>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>