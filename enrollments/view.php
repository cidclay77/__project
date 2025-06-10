<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

if(!isset($_GET["id"]) || empty($_GET["id"])){
    header("location: index.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT e.*, s.name as student_name, s.email as student_email,
               c.name as course_name, c.description as course_description,
               c.workload, c.start_date, c.end_date
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        WHERE e.id = ?
    ");
    $stmt->execute([$_GET["id"]]);
    $enrollment = $stmt->fetch();
    
    if(!$enrollment) {
        header("location: index.php");
        exit;
    }

    // Check if there's a certificate
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE enrollment_id = ?");
    $stmt->execute([$_GET["id"]]);
    $certificate = $stmt->fetch();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Matrícula - Sistema de Cursos</title>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Detalhes da Matrícula</h2>
                <div>
                    <a href="edit.php?id=<?php echo $enrollment['id']; ?>" class="btn btn-warning">
                        <i class='bx bx-edit'></i> Editar
                    </a>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Informações do Aluno</h5>
                            <dl class="row">
                                <dt class="col-sm-3">Nome</dt>
                                <dd class="col-sm-9"><?php echo htmlspecialchars($enrollment['student_name']); ?></dd>

                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9"><?php echo htmlspecialchars($enrollment['student_email']); ?></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informações da Matrícula</h5>
                            <dl class="row">
                                <dt class="col-sm-3">Data</dt>
                                <dd class="col-sm-9"><?php echo date('d/m/Y', strtotime($enrollment['enrollment_date'])); ?></dd>

                                <dt class="col-sm-3">Status</dt>
                                <dd class="col-sm-9">
                                    <span class="badge bg-<?php 
                                        echo $enrollment['status'] === 'active' ? 'success' : 
                                            ($enrollment['status'] === 'completed' ? 'primary' : 'secondary'); 
                                    ?>">
                                        <?php echo $enrollment['status']; ?>
                                    </span>
                                </dd>

                                <?php if($certificate): ?>
                                    <dt class="col-sm-3">Certificado</dt>
                                    <dd class="col-sm-9">
                                        Emitido em <?php echo date('d/m/Y', strtotime($certificate['issue_date'])); ?>
                                        <br>
                                        Número: <?php echo htmlspecialchars($certificate['certificate_number']); ?>
                                    </dd>
                                <?php endif; ?>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informações do Curso</h5>
                            <dl class="row">
                                <dt class="col-sm-3">Nome</dt>
                                <dd class="col-sm-9"><?php echo htmlspecialchars($enrollment['course_name']); ?></dd>

                                <dt class="col-sm-3">Descrição</dt>
                                <dd class="col-sm-9"><?php echo htmlspecialchars($enrollment['course_description']); ?></dd>

                                <dt class="col-sm-3">Carga Horária</dt>
                                <dd class="col-sm-9"><?php echo $enrollment['workload']; ?> horas</dd>

                                <dt class="col-sm-3">Início</dt>
                                <dd class="col-sm-9"><?php echo date('d/m/Y', strtotime($enrollment['start_date'])); ?></dd>

                                <dt class="col-sm-3">Término</dt>
                                <dd class="col-sm-9"><?php echo date('d/m/Y', strtotime($enrollment['end_date'])); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>