<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

// Get instructors for dropdown
try {
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
    $instructors = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $pdo->prepare("INSERT INTO courses (name, description, workload, instructor_id, start_date, end_date, local, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['workload'],
            $_POST['instructor_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['local'],
            $_POST['status']
        ]);
        
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao criar curso: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .card-dashboard {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card-dashboard i {
            font-size: 32px;
        }
    </style>
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
                <a class="nav-link" href="enrollments/">
                    <i class='bx bx-list-check'></i> Matrículas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../certificates/">
                    <i class='bx bx-certification'></i> Certificados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users/">
                    <i class='bx bx-group'></i> Usuários
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class='bx bx-log-out'></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <div class="container mt-4">
        <h2>Novo Curso</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nome do Curso</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Carga Horária (horas)</label>
                <input type="number" class="form-control" name="workload" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Instrutor</label>
                <select class="form-control" name="instructor_id">
                    <option value="">Selecione um instrutor</option>
                    <?php foreach($instructors as $instructor): ?>
                        <option value="<?php echo $instructor['id']; ?>">
                            <?php echo htmlspecialchars($instructor['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Data de Início</label>
                <input type="date" class="form-control" name="start_date" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Data de Término</label>
                <input type="date" class="form-control" name="end_date" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Local do curso</label>
                <input type="text" class="form-control" name="local" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status" required>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Criar Curso</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load dashboard data
            $.get('api/dashboard.php', function(data) {
                $('#totalCourses').text(data.totalCourses);
                $('#totalStudents').text(data.totalStudents);
                $('#activeEnrollments').text(data.activeEnrollments);
                $('#totalCertificates').text(data.totalCertificates);
                
                // Recent courses
                let coursesHtml = '';
                data.recentCourses.forEach(function(course) {
                    coursesHtml += `
                        <tr>
                            <td>${course.name}</td>
                            <td>${course.start_date}</td>
                            <td><span class="badge bg-${course.status === 'active' ? 'success' : 'secondary'}">${course.status}</span></td>
                        </tr>
                    `;
                });
                $('#recentCourses').html(coursesHtml);
                
                // Recent enrollments
                let enrollmentsHtml = '';
                data.recentEnrollments.forEach(function(enrollment) {
                    enrollmentsHtml += `
                        <tr>
                            <td>${enrollment.student_name}</td>
                            <td>${enrollment.course_name}</td>
                            <td>${enrollment.enrollment_date}</td>
                        </tr>
                    `;
                });
                $('#recentEnrollments').html(enrollmentsHtml);
            });
        });
    </script>
</body>
</html>