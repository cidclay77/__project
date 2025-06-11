<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

// Check if user is admin
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["id"]]);
    $current_user = $stmt->fetch();
    
    if(!$current_user || $current_user['role'] !== 'admin') {
        header("location: ../dashboard.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET["id"]]);
    $user = $stmt->fetch();
    
    if(!$user) {
        header("location: index.php");
        exit;
    }

    // Get courses taught by this user (if instructor)
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE instructor_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_GET["id"]]);
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
    <title>Detalhes do Usuário - Sistema de Cursos</title>
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
                <a class="nav-link active" href="../users/">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalhes do Usuário</h2>
                <div>
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">
                        <i class='bx bx-edit'></i> Editar
                    </a>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Informações do Usuário</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Nome de Usuário</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['username']); ?></dd>

                                <dt class="col-sm-4">Nome Completo</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['name']); ?></dd>

                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($user['email']); ?></dd>

                                <dt class="col-sm-4">Função</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'Administrador' : 'Instrutor'; ?>
                                    </span>
                                </dd>

                                <dt class="col-sm-4">Criado em</dt>
                                <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Cursos Ministrados</h5>
                            <?php if($user['role'] === 'instructor'): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Curso</th>
                                                <th>Carga Horária</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($courses)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Nenhum curso atribuído</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach($courses as $course): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($course['name']); ?></td>
                                                        <td><?php echo $course['workload']; ?>h</td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $course['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                                <?php echo $course['status'] === 'active' ? 'Ativo' : 'Inativo'; ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">Este usuário é um administrador e não ministra cursos.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>