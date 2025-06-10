<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

function generateCertificateNumber() {
    $prefix = 'CERT';
    $year = date('Y');
    $randomDigits = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    return $prefix . '-' . $year . '-' . $randomDigits;
}

try {
    // Get completed enrollments without certificates
    $stmt = $pdo->query("
        SELECT e.id, e.enrollment_date, s.name as student_name, c.name as course_name,
               c.workload, c.start_date, c.end_date
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        LEFT JOIN certificates cert ON e.id = cert.enrollment_id
        WHERE e.status = 'completed' AND cert.id IS NULL
        ORDER BY s.name
    ");
    $enrollments = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        // Generate certificate number and check if it's unique
        do {
            $certificate_number = generateCertificateNumber();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM certificates WHERE certificate_number = ?");
            $stmt->execute([$certificate_number]);
            $exists = $stmt->fetchColumn() > 0;
        } while ($exists);

        $stmt = $pdo->prepare("INSERT INTO certificates (enrollment_id, certificate_number, issue_date) VALUES (?, ?, ?)");
        $stmt->execute([
            $_POST['enrollment_id'],
            $certificate_number,
            $_POST['issue_date']
        ]);
        
        $_SESSION['success_message'] = "Certificado criado com sucesso! Número: " . $certificate_number;
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao criar certificado: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Certificado - Sistema de Cursos</title>
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
            <h2 class="mb-4">Novo Certificado</h2>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(empty($enrollments)): ?>
                <div class="alert alert-warning">
                    <h5>Nenhuma matrícula elegível encontrada</h5>
                    <p>Para emitir um certificado, é necessário que haja matrículas com status "concluído" que ainda não possuam certificado.</p>
                    <a href="../enrollments/" class="btn btn-primary">Gerenciar Matrículas</a>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Número do Certificado</label>
                                <input type="text" class="form-control bg-light" value="<?php echo generateCertificateNumber(); ?>" readonly disabled>
                                <small class="text-muted">O número do certificado será gerado automaticamente ao salvar</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Matrícula</label>
                                <select class="form-control" name="enrollment_id" required>
                                    <option value="">Selecione uma matrícula concluída</option>
                                    <?php foreach($enrollments as $enrollment): ?>
                                        <option value="<?php echo $enrollment['id']; ?>">
                                            <?php echo htmlspecialchars($enrollment['student_name'] . ' - ' . $enrollment['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Data de Emissão</label>
                                <input type="date" class="form-control" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Criar Certificado</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>