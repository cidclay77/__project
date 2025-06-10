<?php
session_start();
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $pdo->prepare("INSERT INTO students (
            cir, name, cpf, rg, nascimento, naturalidade, nacionalidade,
            address, bairro, cidade, uf, cep, sexo,
            email, phone, phone1, pai, mae
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $_POST['cir'],
            $_POST['name'],
            $_POST['cpf'],
            $_POST['rg'],
            $_POST['nascimento'],
            $_POST['naturalidade'],
            $_POST['nacionalidade'],
            $_POST['address'],
            $_POST['bairro'],
            $_POST['cidade'],
            $_POST['uf'],
            $_POST['cep'],
            $_POST['sexo'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['phone1'],
            $_POST['pai'],
            $_POST['mae']
        ]);

        header("Location: index.php");
        exit;
    } catch (PDOException $e) {
        $error = "Erro ao criar aluno: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Aluno - Sistema de Cursos</title>
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
            <h2 class="mb-4">Novo Aluno</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h5 class="card-title mb-4 text-primary">Cadastro de Aluno</h5>
                    <form method="POST">
                        <!-- Dados Pessoais -->
                        <div class="mb-4">
                            <h6 class="text-secondary">Dados Pessoais</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">CIR</label>
                                    <input type="text" class="form-control" name="cir" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">CPF</label>
                                    <input type="text" class="form-control" name="cpf" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">RG</label>
                                    <input type="text" class="form-control" name="rg" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Data de Nascimento</label>
                                    <input type="date" class="form-control" name="nascimento" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Naturalidade</label>
                                    <input type="text" class="form-control" name="naturalidade" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nacionalidade</label>
                                    <input type="text" class="form-control" name="nacionalidade" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Sexo</label>
                                    <select class="form-select" name="sexo" required>
                                        <option value="" selected disabled>Selecione</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Feminino">Feminino</option>
                                        <option value="Outro">Outro</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Contato -->
                        <div class="mb-4">
                            <h6 class="text-secondary">Informações de Contato</h6>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Endereço</label>
                                    <input type="text" class="form-control" name="address" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Bairro</label>
                                    <input type="text" class="form-control" name="bairro" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Cidade</label>
                                    <input type="text" class="form-control" name="cidade" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">UF</label>
                                    <input type="text" class="form-control text-uppercase" name="uf" maxlength="2"
                                        required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">CEP</label>
                                    <input type="text" class="form-control" name="cep" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">E-mail</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Celular</label>
                                    <input type="text" class="form-control" name="phone">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Telefone Fixo</label>
                                    <input type="text" class="form-control" name="phone1">
                                </div>
                            </div>
                        </div>

                        <!-- Filiação -->
                        <div class="mb-4">
                            <h6 class="text-secondary">Filiação</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nome do Pai</label>
                                    <input type="text" class="form-control" name="pai">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nome da Mãe</label>
                                    <input type="text" class="form-control" name="mae">
                                </div>
                            </div>
                        </div>

                        <!-- Botões -->
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">Criar Aluno</button>
                            <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>