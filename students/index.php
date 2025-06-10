<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY name");
    $students = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alunos - Sistema de Cursos</title>
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
                <h2>Alunos</h2>
                <a href="create.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Novo Aluno
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Email</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td>
                                        <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-info">
                                            <i class='bx bx-show'></i>
                                        </a>
                                        <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning">
                                            <i class='bx bx-edit'></i>
                                        </a>
                                        <a href="delete.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir este aluno?')">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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