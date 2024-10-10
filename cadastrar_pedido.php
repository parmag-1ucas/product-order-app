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

        echo "<p class='alert alert-success'>Produto adicionado ao pedido!</p>";
    } else {
        echo "<p class='alert alert-danger'>Por favor, preencha todos os campos!</p>";
    }
}

if (isset($_POST['remover_item'])) {
    $produto_id = $_POST['produto_id'];

    foreach ($_SESSION['pedido_itens'] as $key => $item) {
        if ($item['produto_id'] == $produto_id) {
            unset($_SESSION['pedido_itens'][$key]);
            echo "<p class='alert alert-success'>Produto removido do pedido!</p>";
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

        echo "<p class='alert alert-success'>Pedido enviado com sucesso!</p>";
    } else {
        echo "<p class='alert alert-danger'>O pedido está vazio ou o nome do cliente não foi preenchido!</p>";
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
    <script src="https://kit.fontawesome.com/f2f4add29b.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
        <h1 class="text-center">Cadastrar Pedido</h1>

        <form action="cadastrar_pedido.php" method="post" class="mt-4">
            <div class="form-group">
                <label for="cliente">Nome do Cliente:</label>
                <input type="text" name="cliente" id="cliente" class="form-control" value="<?php echo htmlspecialchars($cliente); ?>" required>
            </div>

            <div class="form-group">
                <label for="produto">Produto:</label>
                <select name="produto_id" id="produto" class="form-control">
                    <option value="">Selecione um produto</option>
                    <?php
                    $query = "SELECT * FROM produtos";
                    $result = $mysqli->query($query);
                    while ($produto = $result->fetch_assoc()) {
                        echo "<option value='" . $produto['id'] . "'>" . $produto['nome'] . " (Estoque: " . $produto['estoque'] . ")</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="quantidade">Quantidade:</label>
                <input type="number" name="quantidade" id="quantidade" class="form-control">
            </div>

            <button type="submit" name="adicionar" class="btn btn-primary">Adicionar ao Pedido</button>
        </form>

        <h2 class="mt-5">Itens do Pedido</h2>

        <table class="table table-bordered table-striped mt-3">
            <thead class="thead-dark">
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
                                <form action='cadastrar_pedido.php' method='post' class='d-inline'>
                                    <input type='hidden' name='produto_id' value='" . $item['produto_id'] . "'>
                                    <button type='submit' name='remover_item' class='btn btn-danger'>Remover</button>
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
            <form action="cadastrar_pedido.php" method="post" class="mt-3">
                <input type="hidden" name="cliente" value="<?php echo htmlspecialchars($cliente); ?>">
                <button type="submit" name="enviar" class="btn btn-success">Enviar Pedido</button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary mt-4">Voltar para a Página Inicial</a>
        </div>
    </div>
</body>
</html>

<?php $mysqli->close(); ?>
