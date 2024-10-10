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
</head>
<body>
    <h1>Sistema de Vendas - Pedidos</h1>

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

        echo "<p style='color:green;'>Pedido removido com sucesso!</p>";
    }
    ?>

    <h2>Pedidos Cadastrados</h2>
    <table border="1">
        <thead>
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
                echo "<button type='submit' name='remover'>Remover Pedido</button>";
                echo "</form>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>

    <h2>Produtos em Estoque</h2>
    <table border="1">
        <thead>
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

    <br>
    <a href="cadastrar_pedido.php">Cadastrar Novo Pedido</a>

</body>
</html>