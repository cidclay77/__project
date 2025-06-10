<?php
session_start();
if(!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true){
    header("location: ../index.php");
    exit;
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        // Insert owner data
        $stmt = $pdo->prepare("INSERT INTO owners (name, cpf, rg, birth_date, address, phone, email) VALUES (:name, :cpf, :rg, :birth_date, :address, :phone, :email)");
        $stmt->execute([
            ':name' => $_POST['owner_name'],
            ':cpf' => $_POST['cpf'],
            ':rg' => $_POST['rg'],
            ':birth_date' => $_POST['birth_date'],
            ':address' => $_POST['owner_address'],
            ':phone' => $_POST['phone'],
            ':email' => $_POST['email']
        ]);
        
        $owner_id = $pdo->lastInsertId();

        // Insert property data
        $stmt = $pdo->prepare("INSERT INTO properties (owner_id, name, address, total_area, mechanized_area, capoeira_area, legal_reserve_area, permanent_preservation_area, cib_itr, car_receipt) VALUES (:owner_id, :name, :address, :total_area, :mechanized_area, :capoeira_area, :legal_reserve_area, :permanent_preservation_area, :cib_itr, :car_receipt)");
        $stmt->execute([
            ':owner_id' => $owner_id,
            ':name' => $_POST['property_name'],
            ':address' => $_POST['property_address'],
            ':total_area' => $_POST['total_area'],
            ':mechanized_area' => $_POST['mechanized_area'],
            ':capoeira_area' => $_POST['capoeira_area'],
            ':legal_reserve_area' => $_POST['legal_reserve_area'],
            ':permanent_preservation_area' => $_POST['permanent_preservation_area'],
            ':cib_itr' => $_POST['cib_itr'],
            ':car_receipt' => $_POST['car_receipt']
        ]);

        $property_id = $pdo->lastInsertId();

        // Insert operational info
        foreach ($_POST['culture_type'] as $key => $value) {
            $stmt = $pdo->prepare("INSERT INTO operational_info (property_id, culture_type, product, planted_area, productivity, production) VALUES (:property_id, :culture_type, :product, :planted_area, :productivity, :production)");
            $stmt->execute([
                ':property_id' => $property_id,
                ':culture_type' => $value,
                ':product' => $_POST['product'][$key],
                ':planted_area' => $_POST['planted_area'][$key],
                ':productivity' => $_POST['productivity'][$key],
                ':production' => $_POST['production'][$key]
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Cadastro realizado com sucesso!']);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar: ' . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Propriedade</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .required::after { content: " *"; color: red; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2>Cadastro de Propriedade</h2>
        <form id="propertyForm" method="POST">
            <div class="row">
                <!-- Dados do Proprietário -->
                <div class="col-md-6">
                    <h4>Dados do Proprietário</h4>
                    <div class="form-group">
                        <label class="required">Nome</label>
                        <input type="text" class="form-control" name="owner_name" required>
                    </div>
                    <div class="form-group">
                        <label class="required">CPF</label>
                        <input type="text" class="form-control" name="cpf" required>
                    </div>
                    <div class="form-group">
                        <label>RG</label>
                        <input type="text" class="form-control" name="rg">
                    </div>
                    <div class="form-group">
                        <label>Data de Nascimento</label>
                        <input type="date" class="form-control" name="birth_date">
                    </div>
                    <div class="form-group">
                        <label class="required">Endereço</label>
                        <input type="text" class="form-control" name="owner_address" required>
                    </div>
                    <div class="form-group">
                        <label>Contato</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="form-group">
                        <label>E-mail</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </div>

                <!-- Dados da Propriedade -->
                <div class="col-md-6">
                    <h4>Dados da Propriedade</h4>
                    <div class="form-group">
                        <label class="required">Nome da Propriedade</label>
                        <input type="text" class="form-control" name="property_name" required>
                    </div>
                    <div class="form-group">
                        <label>Endereço/Referência</label>
                        <input type="text" class="form-control" name="property_address">
                    </div>
                    <div class="form-group">
                        <label>Área Total (ha)</label>
                        <input type="number" step="0.01" class="form-control" name="total_area">
                    </div>
                    <div class="form-group">
                        <label>Área Mecanizada (ha)</label>
                        <input type="number" step="0.01" class="form-control" name="mechanized_area">
                    </div>
                    <div class="form-group">
                        <label>Área de Capoeira (ha)</label>
                        <input type="number" step="0.01" class="form-control" name="capoeira_area">
                    </div>
                    <div class="form-group">
                        <label>Área de Reserva Legal (ha)</label>
                        <input type="number" step="0.01" class="form-control" name="legal_reserve_area">
                    </div>
                    <div class="form-group">
                        <label>Área de Preservação Permanente (ha)</label>
                        <input type="number" step="0.01" class="form-control" name="permanent_preservation_area">
                    </div>
                    <div class="form-group">
                        <label>CIB(NIRF)ITR</label>
                        <input type="text" class="form-control" name="cib_itr">
                    </div>
                    <div class="form-group">
                        <label>Nº Recibo do CAR</label>
                        <input type="text" class="form-control" name="car_receipt">
                    </div>
                </div>
            </div>

            <!-- Informações Operacionais -->
            <h4 class="mt-4">Informações Operacionais</h4>
            <div id="operational-info">
                <div class="row operational-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Cultura</label>
                            <select class="form-control" name="culture_type[]" required>
                                <option value="horticultura">Horticultura</option>
                                <option value="fruticultura">Fruticultura</option>
                                <option value="culturas_anuais">Culturas Anuais</option>
                                <option value="sistemas_agroflorestais">Sistemas Agroflorestais</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Produto</label>
                            <input type="text" class="form-control" name="product[]" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Área Plantada (ha)</label>
                            <input type="number" step="0.01" class="form-control" name="planted_area[]" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Produtividade</label>
                            <input type="number" step="0.01" class="form-control" name="productivity[]" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Produção</label>
                            <input type="number" step="0.01" class="form-control" name="production[]" required>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-secondary mt-2" id="add-row">Adicionar Linha</button>
            <button type="submit" class="btn btn-primary mt-2">Cadastrar</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#add-row').click(function() {
                const newRow = $('.operational-row').first().clone();
                newRow.find('input').val('');
                $('#operational-info').append(newRow);
            });

            $('#propertyForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            $('#propertyForm')[0].reset();
                            $('.operational-row:not(:first)').remove();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Erro ao processar a requisição');
                    }
                });
            });
        });
    </script>
</body>
</html>