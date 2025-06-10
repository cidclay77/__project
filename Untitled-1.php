// project -> api -> dashboard.php //

<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    http_response_code(401);
    exit;
}

require_once "../config/database.php";

try {
    $data = [
        'totalCourses' => 0,
        'totalStudents' => 0,
        'activeEnrollments' => 0,
        'totalCertificates' => 0,
        'recentCourses' => [],
        'recentEnrollments' => []
    ];

    // Get totals
    $stmt = $pdo->query("SELECT COUNT(*) FROM courses");
    $data['totalCourses'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $data['totalStudents'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'");
    $data['activeEnrollments'] = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM certificates");
    $data['totalCertificates'] = $stmt->fetchColumn();

    // Get recent courses
    $stmt = $pdo->query("SELECT name, start_date, status FROM courses ORDER BY created_at DESC LIMIT 5");
    $data['recentCourses'] = $stmt->fetchAll();

    // Get recent enrollments
    $stmt = $pdo->query("
        SELECT s.name as student_name, c.name as course_name, e.enrollment_date
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.created_at DESC
        LIMIT 5
    ");
    $data['recentEnrollments'] = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($data);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

// config -> datapase.php //

<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_management');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    // First connect without database selected
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    
    // Reconnect with database selected
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Check if tables exist
    $result = $pdo->query("SHOW TABLES LIKE 'users'")->rowCount();
    if ($result == 0) {
        // Only create tables if they don't exist
        $sql = file_get_contents(__DIR__ . '/../supabase/migrations/20250524201302_lucky_plain.sql');
        // Remove CREATE DATABASE and USE statements
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
        $sql = preg_replace('/USE.*?;/is', '', $sql);
        $pdo->exec($sql);
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// courses -> creat.php //

<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

// Get instructors for dropdown
try {
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY username");
    $instructors = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $pdo->prepare("INSERT INTO courses (name, description, workload, instructor_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['description'],
            $_POST['workload'],
            $_POST['instructor_id'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['status']
        ]);
        
        header("location: index.php");
        exit;
    } catch(PDOException $e) {
        $error = "Erro ao criar curso: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .card-dashboard {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card-dashboard i {
            font-size: 32px;
        }
    </style>
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
                <a class="nav-link" href="enrollments/">
                    <i class='bx bx-list-check'></i> Matrículas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../certificates/">
                    <i class='bx bx-certification'></i> Certificados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users/">
                    <i class='bx bx-group'></i> Usuários
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class='bx bx-log-out'></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <div class="container mt-4">
        <h2>Novo Curso</h2>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nome do Curso</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Descrição</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Carga Horária (horas)</label>
                <input type="number" class="form-control" name="workload" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Instrutor</label>
                <select class="form-control" name="instructor_id">
                    <option value="">Selecione um instrutor</option>
                    <?php foreach($instructors as $instructor): ?>
                        <option value="<?php echo $instructor['id']; ?>">
                            <?php echo htmlspecialchars($instructor['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Data de Início</label>
                <input type="date" class="form-control" name="start_date" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Data de Término</label>
                <input type="date" class="form-control" name="end_date" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-control" name="status" required>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Criar Curso</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load dashboard data
            $.get('api/dashboard.php', function(data) {
                $('#totalCourses').text(data.totalCourses);
                $('#totalStudents').text(data.totalStudents);
                $('#activeEnrollments').text(data.activeEnrollments);
                $('#totalCertificates').text(data.totalCertificates);
                
                // Recent courses
                let coursesHtml = '';
                data.recentCourses.forEach(function(course) {
                    coursesHtml += `
                        <tr>
                            <td>${course.name}</td>
                            <td>${course.start_date}</td>
                            <td><span class="badge bg-${course.status === 'active' ? 'success' : 'secondary'}">${course.status}</span></td>
                        </tr>
                    `;
                });
                $('#recentCourses').html(coursesHtml);
                
                // Recent enrollments
                let enrollmentsHtml = '';
                data.recentEnrollments.forEach(function(enrollment) {
                    enrollmentsHtml += `
                        <tr>
                            <td>${enrollment.student_name}</td>
                            <td>${enrollment.course_name}</td>
                            <td>${enrollment.enrollment_date}</td>
                        </tr>
                    `;
                });
                $('#recentEnrollments').html(enrollmentsHtml);
            });
        });
    </script>
</body>
</html>

// project -> dashboard.php //

<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.8);
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .card-dashboard {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card-dashboard i {
            font-size: 32px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="px-3 mb-4">
            <h4>Sistema de Cursos</h4>
            <small>Bem-vindo, <?php echo htmlspecialchars($_SESSION["username"]); ?></small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class='bx bx-home'></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="courses/">
                    <i class='bx bx-book'></i> Cursos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="students/">
                    <i class='bx bx-user'></i> Alunos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="enrollments/">
                    <i class='bx bx-list-check'></i> Matrículas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="certificates/">
                    <i class='bx bx-certification'></i> Certificados
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users/">
                    <i class='bx bx-group'></i> Usuários
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="logout.php">
                    <i class='bx bx-log-out'></i> Sair
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Dashboard</h2>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-book text-primary'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Total de Cursos</h6>
                                    <h3 class="mb-0" id="totalCourses">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-user text-success'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Total de Alunos</h6>
                                    <h3 class="mb-0" id="totalStudents">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-list-check text-warning'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Matrículas Ativas</h6>
                                    <h3 class="mb-0" id="activeEnrollments">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-dashboard mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-certification text-info'></i>
                                <div class="ms-3">
                                    <h6 class="mb-0">Certificados Emitidos</h6>
                                    <h3 class="mb-0" id="totalCertificates">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-dashboard">
                        <div class="card-body">
                            <h5 class="card-title">Últimos Cursos</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Curso</th>
                                            <th>Início</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentCourses">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-dashboard">
                        <div class="card-body">
                            <h5 class="card-title">Últimas Matrículas</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Aluno</th>
                                            <th>Curso</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentEnrollments">
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load dashboard data
            $.get('api/dashboard.php', function(data) {
                $('#totalCourses').text(data.totalCourses);
                $('#totalStudents').text(data.totalStudents);
                $('#activeEnrollments').text(data.activeEnrollments);
                $('#totalCertificates').text(data.totalCertificates);
                
                // Recent courses
                let coursesHtml = '';
                data.recentCourses.forEach(function(course) {
                    coursesHtml += `
                        <tr>
                            <td>${course.name}</td>
                            <td>${course.start_date}</td>
                            <td><span class="badge bg-${course.status === 'active' ? 'success' : 'secondary'}">${course.status}</span></td>
                        </tr>
                    `;
                });
                $('#recentCourses').html(coursesHtml);
                
                // Recent enrollments
                let enrollmentsHtml = '';
                data.recentEnrollments.forEach(function(enrollment) {
                    enrollmentsHtml += `
                        <tr>
                            <td>${enrollment.student_name}</td>
                            <td>${enrollment.course_name}</td>
                            <td>${enrollment.enrollment_date}</td>
                        </tr>
                    `;
                });
                $('#recentEnrollments').html(enrollmentsHtml);
            });
        });
    </script>
</body>
</html> 

// project -> index.php //

<?php
session_start();
if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true){
    header("location: dashboard.php");
    exit;
}

require_once "config/database.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["username"]))){
        $username_err = "Por favor, digite o nome de usuário.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Por favor, digite sua senha.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = trim($_POST["username"]);
            
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    if($row = $stmt->fetch()){
                        $id = $row["id"];
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        
                        if(password_verify($password, $hashed_password)){
                            session_start();
                            
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            
                            header("location: dashboard.php");
                        } else{
                            $login_err = "Nome de usuário ou senha inválidos.";
                        }
                    }
                } else{
                    $login_err = "Nome de usuário ou senha inválidos.";
                }
            } else{
                echo "Ops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            unset($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo i {
            font-size: 48px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <i class='bx bx-book-reader'></i>
                <h2>Sistema de Cursos</h2>
            </div>

            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label class="form-label">Nome de usuário</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class='bx bx-user'></i></span>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    </div>
                    <div class="invalid-feedback"><?php echo $username_err; ?></div>
                </div>    
                <div class="mb-3">
                    <label class="form-label">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class='bx bx-lock-alt'></i></span>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    </div>
                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

// course_management.sql //
-- Estrutura para tabela `communities`
--

CREATE TABLE `communities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `zone` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `workload` int(11) NOT NULL,
  `instructor_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `courses`
--

INSERT INTO `courses` (`id`, `name`, `description`, `workload`, `instructor_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Licenciamento Ambiental', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.', 40, 1, '2025-05-26', '2025-05-30', 'active', '2025-05-26 13:43:55', '2025-05-26 13:44:29'),
(2, 'servicos-de-saude', 'This handy tool helps you create dummy text for all your layout needs. We are gradually adding new functionality and we welcome your suggestions and feedback.', 60, 1, '2025-06-02', '2025-06-10', 'active', '2025-05-26 14:00:44', '2025-05-26 14:04:38');

-- --------------------------------------------------------

--
-- Estrutura para tabela `operational_info`
--

CREATE TABLE `operational_info` (
  `id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `culture_type` enum('horticultura','fruticultura','culturas_anuais','sistemas_agroflorestais','outros') DEFAULT NULL,
  `product` varchar(100) DEFAULT NULL,
  `planted_area` decimal(10,2) DEFAULT NULL,
  `productivity` decimal(10,2) DEFAULT NULL,
  `production` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `owners`
--

CREATE TABLE `owners` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `rg` varchar(20) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `total_area` decimal(10,2) DEFAULT NULL,
  `mechanized_area` decimal(10,2) DEFAULT NULL,
  `capoeira_area` decimal(10,2) DEFAULT NULL,
  `legal_reserve_area` decimal(10,2) DEFAULT NULL,
  `permanent_preservation_area` decimal(10,2) DEFAULT NULL,
  `cib_itr` varchar(50) DEFAULT NULL,
  `car_receipt` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$joNIUO9Be.K8pFbRix/52e1vr1zD/PwquLEUlCz9FIbNZwlxHiy6C', '2025-05-26 13:29:58');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Índices de tabela `operational_info`
--
ALTER TABLE `operational_info`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Índices de tabela `owners`
--
ALTER TABLE `owners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `operational_info`
--
ALTER TABLE `operational_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `owners`
--
ALTER TABLE `owners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `operational_info`
--
ALTER TABLE `operational_info`
  ADD CONSTRAINT `operational_info_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Restrições para tabelas `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `owners` (`id`);
COMMIT;

