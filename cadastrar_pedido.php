<?php
require 'db.php';

$cliente = isset($_POST['cliente']) ? $_POST['cliente'] : '';

session_start();

if (isset($_POST['adicionar'])) {
    $cliente = $_POST['cliente'];
    $produto_id = $_POST['produto_id'];
    $quantidade = $_POST['quantidade'];

    if (!empty($cliente) && !empty($produto_id) && !empty($quantidade)) {
        if (!isset($_SESSION['pedido_itens'])) {
            $_SESSION['pedido_itens'] = [];
        }

        $produto_existente = false;
        foreach ($_SESSION['pedido_itens'] as &$item) {
            if ($item['produto_id'] == $produto_id) {
                $item['quantidade'] += $quantidade;
                $produto_existente = true;
                break;
            }
        }

        if (!$produto_existente) {
            $_SESSION['pedido_itens'][] = ['produto_id' => $produto_id, 'quantidade' => $quantidade];
        }

        echo "<p style='color:green;'>Produto adicionado ao pedido!</p>";
    } else {
        echo "<p style='color:red;'>Por favor, preencha todos os campos!</p>";
    }
}

if (isset($_POST['remover_item'])) {
    $produto_id = $_POST['produto_id'];

    foreach ($_SESSION['pedido_itens'] as $key => $item) {
        if ($item['produto_id'] == $produto_id) {
            unset($_SESSION['pedido_itens'][$key]);
            echo "<p style='color:green;'>Produto removido do pedido!</p>";
            break;
        }
    }
    $_SESSION['pedido_itens'] = array_values($_SESSION['pedido_itens']);
}

if (isset($_POST['enviar'])) {
    if (!empty($_SESSION['pedido_itens']) && !empty($_POST['cliente'])) {
        $cliente = $_POST['cliente'];

        $quantidade_total = 0;
        foreach ($_SESSION['pedido_itens'] as $item) {
            $quantidade_total += $item['quantidade'];
        }

        $query = "INSERT INTO pedidos (cliente, quantidade_total) VALUES (?, ?)";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param("si", $cliente, $quantidade_total);
        $stmt->execute();
        $pedido_id = $mysqli->insert_id;
        $stmt->close();

        foreach ($_SESSION['pedido_itens'] as $item) {
            $query = "INSERT INTO pedido_itens (pedido_id, produto_id, quantidade) VALUES (?, ?, ?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("iii", $pedido_id, $item['produto_id'], $item['quantidade']);
            $stmt->execute();

            $query = "UPDATE produtos SET estoque = estoque - ? WHERE id = ?";
            $stmt_update = $mysqli->prepare($query);
            $stmt_update->bind_param("ii", $item['quantidade'], $item['produto_id']);
            $stmt_update->execute();
            $stmt_update->close();
        }

        unset($_SESSION['pedido_itens']);

        echo "<p style='color:green;'>Pedido enviado com sucesso!</p>";
    } else {
        echo "<p style='color:red;'>O pedido está vazio ou o nome do cliente não foi preenchido!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Pedido</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Cadastrar Pedido</h1>

    <form action="cadastrar_pedido.php" method="post">
        <label for="cliente">Nome do Cliente:</label>
        <input type="text" name="cliente" id="cliente" value="<?php echo htmlspecialchars($cliente); ?>" required><br><br>

        <label for="produto">Produto:</label>
        <select name="produto_id" id="produto">
            <option value="">Selecione um produto</option>
            <?php
            $query = "SELECT * FROM produtos";
            $result = $mysqli->query($query);
            while ($produto = $result->fetch_assoc()) {
                echo "<option value='" . $produto['id'] . "'>" . $produto['nome'] . " (Estoque: " . $produto['estoque'] . ")</option>";
            }
            ?>
        </select><br><br>

        <label for="quantidade">Quantidade:</label>
        <input type="number" name="quantidade" id="quantidade"><br><br>

        <button type="submit" name="adicionar">Adicionar ao Pedido</button>
    </form>

    <h2>Itens do Pedido</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($_SESSION['pedido_itens']) && !empty($_SESSION['pedido_itens'])) {
                foreach ($_SESSION['pedido_itens'] as $item) {
                    $query = "SELECT nome FROM produtos WHERE id = ?";
                    $stmt = $mysqli->prepare($query);
                    $stmt->bind_param("i", $item['produto_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $produto = $result->fetch_assoc();

                    echo "<tr>";
                    echo "<td>" . $produto['nome'] . "</td>";
                    echo "<td>" . $item['quantidade'] . "</td>";
                    echo "<td>
                            <form action='cadastrar_pedido.php' method='post' style='display:inline-block;'>
                                <input type='hidden' name='produto_id' value='" . $item['produto_id'] . "'>
                                <button type='submit' name='remover_item'>Remover</button>
                            </form>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>Nenhum item no pedido.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if (isset($_SESSION['pedido_itens']) && !empty($_SESSION['pedido_itens'])): ?>
        <form action="cadastrar_pedido.php" method="post">
            <input type="hidden" name="cliente" value="<?php echo htmlspecialchars($cliente); ?>">
            <button type="submit" name="enviar">Enviar Pedido</button>
        </form>
    <?php endif; ?>

    <br>
    <a href="index.php">Voltar para a Página Inicial</a>
</body>
</html>

<?php $mysqli->close(); ?>
