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
</head>
<body>
    <h1>Cadastrar Produto</h1>

    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form action="cadastrar_produto.php" method="post">
        <label for="nome">Nome do Produto:</label>
        <input type="text" name="nome" id="nome" required><br><br>

        <label for="estoque">Estoque:</label>
        <input type="number" name="estoque" id="estoque" min="0" required><br><br>

        <button type="submit">Cadastrar Produto</button>
    </form>

    <br>
    <a href="index.php">Voltar para a página inicial</a>

</body>
</html>

<?php $mysqli->close(); ?>
