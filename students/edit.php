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
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$_GET["id"]]);
    $student = $stmt->fetch();
    
    if(!$student) {
        header("location: index.php");
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $pdo->prepare("
            UPDATE students SET 
                cir = ?, 
                name = ?, 
                cpf = ?, 
                rg = ?, 
                nascimento = ?, 
                naturalidade = ?, 
                nacionalidade = ?, 
                sexo = ?, 
                email = ?, 
                phone = ?, 
                phone1 = ?, 
                address = ?, 
                bairro = ?, 
                cidade = ?, 
                uf = ?, 
                cep = ?, 
                pai = ?, 
                mae = ?, 
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['cir'],
            $_POST['name'],
            $_POST['cpf'],
            $_POST['rg'],
            $_POST['nascimento'],
            $_POST['natural'],
            $_POST['nacional'],
            $_POST['sexo'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['phone1'],
            $_POST['address'],
            $_POST['bairro'],
            $_POST['cidcade'], // Obs: corrigir o `name` no formulário HTML se necessário
            $_POST['uf'],
            $_POST['cep'],
            $_POST['pai'],
            $_POST['mae'],
            $_GET['id']
        ]);

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = "Erro ao atualizar aluno: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno - Sistema de Cursos</title>
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
            <h2 class="mb-4">Editar Aluno</h2>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
    <div class="mb-3">
        <label class="form-label">CIR</label>
        <input type="text" class="form-control" name="cir" value="<?= htmlspecialchars($student['cir']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">CPF</label>
        <input type="text" class="form-control" name="cpf" value="<?= htmlspecialchars($student['cpf']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">RG</label>
        <input type="text" class="form-control" name="rg" value="<?= htmlspecialchars($student['rg']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nascimento</label>
        <input type="date" class="form-control" name="nascimento" value="<?= htmlspecialchars($student['nascimento']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Naturalidade</label>
        <input type="text" class="form-control" name="natural" value="<?= htmlspecialchars($student['naturalidade']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nacionalidade</label>
        <input type="text" class="form-control" name="nacional" value="<?= htmlspecialchars($student['nacionalidade']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Sexo</label>
        <input type="text" class="form-control" name="sexo" value="<?= htmlspecialchars($student['sexo']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Contato</label>
        <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($student['phone']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Telefone</label>
        <input type="text" class="form-control" name="phone1" value="<?= htmlspecialchars($student['phone1']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Endereço</label>
        <textarea class="form-control" name="address" rows="3"><?= htmlspecialchars($student['address']) ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Bairro</label>
        <input type="text" class="form-control" name="bairro" value="<?= htmlspecialchars($student['bairro']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Cidade</label>
        <input type="text" class="form-control" name="cidade" value="<?= htmlspecialchars($student['cidade']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Estado</label>
        <input type="text" class="form-control" name="uf" value="<?= htmlspecialchars($student['uf']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">CEP</label>
        <input type="text" class="form-control" name="cep" value="<?= htmlspecialchars($student['cep']) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Nome do Pai</label>
        <input type="text" class="form-control" name="pai" value="<?= htmlspecialchars($student['pai']) ?>">
    </div>

    <div class="mb-3">
        <label class="form-label">Nome da Mãe</label>
        <input type="text" class="form-control" name="mae" value="<?= htmlspecialchars($student['mae']) ?>">
    </div>

    <button type="submit" class="btn btn-primary">Atualizar Aluno</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
</form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>