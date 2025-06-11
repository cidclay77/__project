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
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    try {
        // Check if username already exists (excluding current user)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$_POST['username'], $_GET['id']]);
        if($stmt->fetchColumn() > 0) {
            $error = "Nome de usuário já existe.";
        } else {
            // Check if email already exists (excluding current user)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$_POST['email'], $_GET['id']]);
            if($stmt->fetchColumn() > 0) {
                $error = "Email já está em uso.";
            } else {
                // Update user data
                if(!empty($_POST['password'])) {
                    // Update with new password
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, name = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['username'],
                        $hashed_password,
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['role'],
                        $_GET['id']
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, email = ?, role = ? WHERE id = ?");
                    $stmt->execute([
                        $_POST['username'],
                        $_POST['name'],
                        $_POST['email'],
                        $_POST['role'],
                        $_GET['id']
                    ]);
                }
                
                $_SESSION['success_message'] = "Usuário atualizado com sucesso!";
                header("location: index.php");
                exit;
            }
        }
    } catch(PDOException $e) {
        $error = "Erro ao atualizar usuário: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Sistema de Cursos</title>
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
            <h2 class="mb-4">Editar Usuário</h2>

            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nome de Usuário</label>
                                    <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    <small class="text-muted">Usado para fazer login no sistema</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nome Completo</label>
                                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Função</label>
                                    <select class="form-control" name="role" required>
                                        <option value="">Selecione uma função</option>
                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="instructor" <?php echo $user['role'] === 'instructor' ? 'selected' : ''; ?>>Instrutor</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" name="password" minlength="6">
                            <small class="text-muted">Deixe em branco para manter a senha atual. Mínimo de 6 caracteres para nova senha.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">Atualizar Usuário</button>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>