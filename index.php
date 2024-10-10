<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Sistema de Vendas</title>
    <script src="https://kit.fontawesome.com/f2f4add29b.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body>
    <div class="container text-center mt-5">    
        <h1>Sistema de Vendas - Pedidos</h1>
    </div>

    <?php
    if (isset($_POST['remover'])) {
        $pedido_id = $_POST['pedido_id'];

        $query = "SELECT * FROM pedido_itens WHERE pedido_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($item = $result->fetch_assoc()) {
            $query = "UPDATE produtos SET estoque = estoque + ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($query);
            $stmt_update->bind_param("ii", $item['quantidade'], $item['produto_id']);
            $stmt_update->execute();
            $stmt_update->close();
        }

        $query = "DELETE FROM pedido_itens WHERE pedido_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();

        $query = "DELETE FROM pedidos WHERE id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("i", $pedido_id);
        $stmt->execute();
        $stmt->close();

        echo "<p class='alert alert-success'>Pedido removido com sucesso!</p>";
    }
    ?>
    <div class="container mt-5">
        <h2 class="text-center">Pedidos Cadastrados</h>
        <table class="table table-bordered table-striped mt-4">
            <thead class="thead-dark">
                <tr>
                    <th>ID do Pedido</th>
                    <th>Cliente</th>
                    <th>Quantidade Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM pedidos";
                $result = $mysqli->query($query);

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['cliente'] . "</td>";
                    echo "<td>" . $row['quantidade_total'] . "</td>";
                    echo "<td>";
                    echo "<form action='index.php' method='post' style='display:inline;'>";
                    echo "<input type='hidden' name='pedido_id' value='" . $row['id'] . "'>";
                    echo "<button type='submit' name='remover' class='btn btn-link text-danger'><i class='fa fa-trash'></i></button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="text-center mt-3">
            <a class="btn btn-dark" href="cadastrar_pedido.php">Cadastrar Novo Pedido</a>
        </div>
    </div>

    <div class="container mt-5">
        <h2 class="text-center">Produtos em Estoque</h2>
        <table class="table table-bordered table-striped mt-4">
            <thead class="thead-dark">
                <tr>
                    <th>ID do Produto</th>
                    <th>Nome</th>
                    <th>Estoque</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM produtos";
                $result = $mysqli->query($query);

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['nome'] . "</td>";
                    echo "<td>" . $row['estoque'] . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="text-center mt-3">
            <a class="btn btn-dark" href="cadastrar_produto.php">Cadastrar Novo Produto</a>
        </div>
    </div>

</body>
</html>