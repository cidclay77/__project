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
    // Get student details
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET["id"]]);
    $student = $stmt->fetch();
    
    if(!$student) {
        header("location: index.php");
        exit;
    }

    // Get student's enrollments along with student's details
$stmt = $pdo->prepare("
    SELECT e.*, c.name as course_name, c.start_date, c.end_date, 
           s.name as student_name, s.cir, s.cpf, s.rg, s.nascimento, s.naturalidade, s.nacionalidade, 
           s.address, s.bairro, s.cidade, s.uf, s.cep, s.sexo, s.email, s.phone, s.phone1, s.pai, s.mae
    FROM enrollments e
    JOIN courses c ON e.course_id = c.id
    JOIN students s ON e.student_id = s.id
    WHERE e.student_id = ?
    ORDER BY e.enrollment_date DESC
");

    $stmt->execute([$_GET["id"]]);
    $enrollments = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Aluno - Sistema de Cursos</title>
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
                <a class="nav-link active" href="../students/">
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
                <h2>Detalhes do Aluno</h2>
                <div>
                    <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">
                        <i class='bx bx-edit'></i> Editar
                    </a>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Informações Pessoais</h5>
            <dl class="row">
                <dt class="col-sm-4">CIR</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['cir']); ?></dd>

                <dt class="col-sm-4">Nome</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['name']); ?></dd>

                <dt class="col-sm-4">CPF</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['cpf']); ?></dd>

                <dt class="col-sm-4">RG</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['rg']); ?></dd>

                <dt class="col-sm-4">Nascimento</dt>
                <dd class="col-sm-8"><?php echo date('d/m/Y', strtotime($student['nascimento'])); ?></dd>

                <dt class="col-sm-4">Naturalidade</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['naturalidade']); ?></dd>

                <dt class="col-sm-4">Nacionalidade</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['nacionalidade']); ?></dd>

                <dt class="col-sm-4">Sexo</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['sexo']); ?></dd>

                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['email']); ?></dd>

                <dt class="col-sm-4">Telefone</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['phone']); ?></dd>

                <dt class="col-sm-4">Telefone Alternativo</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['phone1']); ?></dd>

                <dt class="col-sm-4">Endereço</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['address']); ?></dd>

                <dt class="col-sm-4">Bairro</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['bairro']); ?></dd>

                <dt class="col-sm-4">Cidade</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['cidade']); ?></dd>

                <dt class="col-sm-4">Estado</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['uf']); ?></dd>

                <dt class="col-sm-4">CEP</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['cep']); ?></dd>

                <dt class="col-sm-4">Nome do Pai</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['pai']); ?></dd>

                <dt class="col-sm-4">Nome da Mãe</dt>
                <dd class="col-sm-8"><?php echo htmlspecialchars($student['mae']); ?></dd>

                <dt class="col-sm-4">Cadastrado em</dt>
                <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($student['created_at'])); ?></dd>
            </dl>
        </div>
    </div>
</div>


                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Matrículas</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Curso</th>
                                            <th>Data da Matrícula</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(empty($enrollments)): ?>
                                            <tr>
                                                <td colspan="3" class="text-center">Nenhuma matrícula encontrada</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach($enrollments as $enrollment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($enrollment['course_name']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($enrollment['enrollment_date'])); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $enrollment['status'] === 'active' ? 'success' : ($enrollment['status'] === 'completed' ? 'primary' : 'secondary'); ?>">
                                                            <?php echo $enrollment['status']; ?>
                                                        </span>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>