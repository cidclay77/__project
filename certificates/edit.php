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
    // Get certificate details
    $stmt = $pdo->prepare("
        SELECT c.*, e.enrollment_date, s.name as student_name, co.name as course_name 
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

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        $stmt = $pdo->prepare("UPDATE certificates SET issue_date = ? WHERE id = ?");
        $stmt->execute([
            $_POST['issue_date'],
            $_GET['id']
        ]);
        
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao atualizar certificado: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Certificado - Sistema de Cursos</title>
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
            <h2 class="mb-4">Editar Certificado</h2>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Número do Certificado</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($certificate['certificate_number']); ?>" readonly disabled>
                            <small class="text-muted">O número do certificado não pode ser alterado</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Aluno</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($certificate['student_name']); ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Curso</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($certificate['course_name']); ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data de Emissão</label>
                            <input type="date" class="form-control" name="issue_date" value="<?php echo $certificate['issue_date']; ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Atualizar Certificado</button>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>