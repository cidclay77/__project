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
        SELECT c.*, e.enrollment_date, s.name as student_name, s.cpf as student_cpf,
               co.name as course_name, co.description as course_description,
               co.workload, co.start_date, co.end_date
        FROM certificates c
        JOIN enrollments e ON c.enrollment_id = e.id
        JOIN students s ON e.student_id = s.id
        JOIN courses co ON e.course_id = co.id
        WHERE c.id = ?
    ");
    $stmt->execute([$_GET["id"]]);
    $certificate = $stmt->fetch();
    
    if(!$certificate) {
        header("location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Certificado - Sistema de Cursos</title>
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
                <a class="nav-link active" href="../certificates/">
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
                <h2>Detalhes do Certificado</h2>
                <div>
                    <a href="print.php?id=<?php echo $certificate['id']; ?>" class="btn btn-success" target="_blank">
                        <i class='bx bx-printer'></i> Imprimir
                    </a>
                    <a href="edit.php?id=<?php echo $certificate['id']; ?>" class="btn btn-warning">
                        <i class='bx bx-edit'></i> Editar
                    </a>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title">Informações do Certificado</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Número</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($certificate['certificate_number']); ?></dd>

                                <dt class="col-sm-4">Data de Emissão</dt>
                                <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($certificate['issue_date'])); ?></dd>

                                <dt class="col-sm-4">Criado em</dt>
                                <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($certificate['created_at'])); ?></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informações do Aluno</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Nome</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($certificate['student_name']); ?></dd>

                                <dt class="col-sm-4">CPF</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($certificate['student_cpf']); ?></dd>

                                <dt class="col-sm-4">Data da Matrícula</dt>
                                <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($certificate['enrollment_date'])); ?></dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Informações do Curso</h5>
                            <dl class="row">
                                <dt class="col-sm-4">Nome</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($certificate['course_name']); ?></dd>

                                <dt class="col-sm-4">Descrição</dt>
                                <dd class="col-sm-8"><?php echo htmlspecialchars($certificate['course_description']); ?></dd>

                                <dt class="col-sm-4">Carga Horária</dt>
                                <dd class="col-sm-8"><?php echo $certificate['workload']; ?> horas</dd>

                                <dt class="col-sm-4">Período</dt>
                                <dd class="col-sm-8">
                                    <?php echo date('d/m/Y', strtotime($certificate['start_date'])); ?> a 
                                    <?php echo date('d/m/Y', strtotime($certificate['end_date'])); ?>
                                </dd>
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