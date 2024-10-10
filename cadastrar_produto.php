<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $estoque = $_POST['estoque'];

    if (empty($nome)) {
        $error = "O nome do produto é obrigatório.";
    } elseif (!is_numeric($estoque) || $estoque < 0) {
        $error = "O estoque deve ser um número válido.";
    } else {
        $query = "INSERT INTO produtos (nome, estoque) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $nome, $estoque);

        if ($stmt->execute()) {
            $success = "Produto cadastrado com sucesso!";
        } else {
            $error = "Erro ao cadastrar produto: " . $mysqli->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Produto</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/f2f4add29b.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mt-4">
            <h1 class="mb-4">Cadastrar Produto</h1>
        </div>

        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form action="cadastrar_produto.php" method="post" class="mb-3">
            <div class="form-group">
                <label for="nome">Nome do Produto:</label>
                <input type="text" name="nome" id="nome" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="estoque">Estoque:</label>
                <input type="number" name="estoque" id="estoque" class="form-control" min="0" required>
            </div>

            <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Voltar para a página inicial</a>
        </div>
    </div>

</body>
</html>

<?php $mysqli->close(); ?>
