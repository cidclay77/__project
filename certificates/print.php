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
    <title>Certificado - <?php echo htmlspecialchars($certificate['student_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .certificate-container { 
                width: 100%; 
                height: 100vh; 
                page-break-after: always;
            }
        }
        
        .certificate-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .certificate {
            background: white;
            width: 800px;
            padding: 60px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            border: 8px solid #f8f9fa;
            position: relative;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
        }
        
        .certificate-header {
            margin-bottom: 40px;
        }
        
        .certificate-title {
            font-size: 3rem;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .certificate-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        .student-name {
            font-size: 2.5rem;
            font-weight: bold;
            color: #343a40;
            margin: 30px 0;
            text-decoration: underline;
            text-decoration-color: #007bff;
        }
        
        .course-info {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #495057;
            margin: 30px 0;
        }
        
        .certificate-footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            align-items: end;
        }
        
        .signature-line {
            width: 200px;
            border-bottom: 2px solid #343a40;
            text-align: center;
            padding-top: 10px;
        }
        
        .certificate-number {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: #007bff;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
    </style>
</head>
<body>
    <div class="certificate-container">
        <div class="certificate">
            <div class="certificate-header">
                <div class="logo">
                    <i class="fas fa-graduation-cap"></i>📜
                </div>
                <h1 class="certificate-title">Certificado</h1>
                <p class="certificate-subtitle">de Conclusão de Curso</p>
            </div>
            
            <div class="certificate-body">
                <p style="font-size: 1.2rem; margin-bottom: 20px;">
                    Certificamos que
                </p>
                
                <h2 class="student-name"><?php echo htmlspecialchars($certificate['student_name']); ?></h2>
                
                <div class="course-info">
                    <p>concluiu com êxito o curso de</p>
                    <p><strong><?php echo htmlspecialchars($certificate['course_name']); ?></strong></p>
                    <p>com carga horária de <strong><?php echo $certificate['workload']; ?> horas</strong></p>
                    <p>realizado no período de <?php echo date('d/m/Y', strtotime($certificate['start_date'])); ?> 
                    a <?php echo date('d/m/Y', strtotime($certificate['end_date'])); ?></p>
                </div>
            </div>
            
            <div class="certificate-footer">
                <div class="signature-line">
                    <small>Coordenação do Curso</small>
                </div>
                <div style="text-align: center;">
                    <p style="margin: 0; font-size: 0.9rem; color: #6c757d;">
                        Emitido em <?php echo date('d/m/Y', strtotime($certificate['issue_date'])); ?>
                    </p>
                </div>
                <div class="signature-line">
                    <small>Direção</small>
                </div>
            </div>
            
            <div class="certificate-number">
                Certificado Nº: <?php echo htmlspecialchars($certificate['certificate_number']); ?>
            </div>
        </div>
    </div>
    
    <div class="no-print text-center mt-3">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Imprimir Certificado
        </button>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>