<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

try {
    $stmt = $pdo->query("SELECT c.*, u.username as instructor_name 
                         FROM courses c 
                         LEFT JOIN users u ON c.instructor_id = u.id 
                         ORDER BY c.created_at DESC");
    $courses = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
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
                <a class="nav-link" href="../enrollments/">
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

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Cursos</h2>
            <a href="create.php" class="btn btn-primary">
                <i class='bx bx-plus'></i> Novo Curso
            </a>
        

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Instrutor</th>
                        <th>Carga Horária</th>
                        <th>Início</th>
                        <th>Término</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($courses as $course): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                        <td><?php echo htmlspecialchars($course['instructor_name'] ?? 'Não atribuído'); ?></td>
                        <td><?php echo htmlspecialchars($course['workload']); ?> horas</td>
                        <td><?php echo date('d/m/Y', strtotime($course['start_date'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($course['end_date'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $course['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo $course['status'] === 'active' ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $course['id']; ?>" class="btn btn-sm btn-primary">
                                <i class='bx bx-edit'></i>
                            </a>
                            <a href="delete.php?id=<?php echo $enrollment['id']; ?>" class="btn btn-sm btn-danger">
                                            <i class='bx bx-trash'></i>
                                        </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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