<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

// Get user role for menu display
$user_role = 'instructor'; // default
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["id"]]);
    $user = $stmt->fetch();
    if($user) {
        $user_role = $user['role'];
    }
} catch(PDOException $e) {
    // Continue with default role
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
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h4>Sistema de Cursos</h4>
            <small>Bem-vindo, <?php echo htmlspecialchars($_SESSION["username"]); ?></small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class='bx bx-home'></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="courses/">
                    <i class='bx bx-book'></i> Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="students/">
                    <i class='bx bx-user'></i> Alunos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="enrollments/">
                    <i class='bx bx-list-check'></i> Matrículas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="certificates/">
                    <i class='bx bx-certification'></i> Certificados
                </a>
            </li>
            <?php if($user_role === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="users/">
                    <i class='bx bx-group'></i> Usuários
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class='bx bx-log-out'></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Dashboard</h2>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-book text-primary'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Total de Cursos</h6>
                                    <h3 class="mb-0" id="totalCourses">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-user text-success'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Total de Alunos</h6>
                                    <h3 class="mb-0" id="totalStudents">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-list-check text-warning'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Matrículas Ativas</h6>
                                    <h3 class="mb-0" id="activeEnrollments">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-certification text-info'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Certificados Emitidos</h6>
                                    <h3 class="mb-0" id="totalCertificates">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-dashboard">
                        <div class="card-body">
                            <h5 class="card-title">Últimos Cursos</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Curso</th>
                                            <th>Início</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentCourses">
                                        <tr>
                                            <td colspan="3" class="text-center">Carregando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-dashboard">
                        <div class="card-body">
                            <h5 class="card-title">Últimas Matrículas</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Aluno</th>
                                            <th>Curso</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentEnrollments">
                                        <tr>
                                            <td colspan="3" class="text-center">Carregando...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>