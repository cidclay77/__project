<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

try {
    $stmt = $pdo->query("
        SELECT c.*, e.enrollment_date, s.name as student_name, co.name as course_name 
        FROM certificates c
        JOIN enrollments e ON c.enrollment_id = e.id
        JOIN students s ON e.student_id = s.id
        JOIN courses co ON e.course_id = co.id
        ORDER BY c.issue_date DESC
    ");
    $certificates = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificados - Sistema de Cursos</title>
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
                <h2>Certificados</h2>
                <a href="create.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Novo Certificado
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Número do Certificado</th>
                                    <th>Aluno</th>
                                    <th>Curso</th>
                                    <th>Data de Emissão</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($certificates)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhum certificado encontrado</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($certificates as $certificate): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($certificate['certificate_number']); ?></td>
                                        <td><?php echo htmlspecialchars($certificate['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($certificate['course_name']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($certificate['issue_date'])); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $certificate['id']; ?>" class="btn btn-sm btn-info">
                                                <i class='bx bx-show'></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $certificate['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class='bx bx-edit'></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $certificate['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este certificado?')">
                                                <i class='bx bx-trash'></i>
                                            </a>
                                            <a href="print.php?id=<?php echo $certificate['id']; ?>" class="btn btn-sm btn-success" target="_blank">
                                                <i class='bx bx-printer'></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>