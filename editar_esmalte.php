<?php
// Começar a sessão
session_start();

// Se não está logado, vai para o login
if (!isset($_SESSION['usuario'])) { 
    header('Location: login.php'); 
    exit(); 
}

// Conectar no banco
include 'config.php';

// Pegar o ID da pizza que queremos editar
$esmalte_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar os dados da pizza
$sql = "SELECT * FROM esmaltes WHERE id = $esmalte_id";
$resultado = $conn->query($sql);
$esmalte = $resultado->fetch_assoc();

// Se não encontrou a pizza, voltar para a lista
if (!$esmalte) { 
    header('Location: esmaltes.php'); 
    exit(); 
}

// Se o usuário clicou em "Atualizar"
if (isset($_POST['update'])) {
    $nome_esmaltes = trim($_POST['nome'] ?? '');
    $cores_esmaltes = trim($_POST['cores'] ?? '');
    $preco_esmaltes = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $categorias_esmaltes = trim($_POST['categorias'] ?? '');
    $marcas_esmaltes = trim($_POST['marcas'] ?? '');
    $estoque_minimo_esmaltes = trim($_POST['estoque_minimo'] ?? '0');

    $erros = array();
    if ($nome_esmalte === '') { $erros[] = 'Informe o nome.'; }
    if ($cores_esmalte === '') { $erros[] = 'Informe os cores.'; }
    if ($categorias_esmalte === '') { $erros[] = 'Informe o categorias.'; }
    if ($marcas_esmalte === '') { $erros[] = 'Informe a marcas.'; }
    if (!is_numeric($preco_esmalte)) { $erros[] = 'Preço inválido.'; }
    if (!ctype_digit((string)$estoque_minimo_esmalte)) { $erros[] = 'Estoque mínimo inválido.'; }

    // Normalizar tamanho
    $map_categorias = array('Cremoso'=>'Cremoso','Metalico'=>'Metalico','Glitter'=>'Glitter','Perolado'=>'Perolado','Fosco'=>'Fosco');
    if (isset($map_categorias[$categorias_esmalte])) { $categorias_esmalte = $map_categorias[$categorias_esmalte]; }
    if (!in_array($categorias_esmalte, array('Pequena','Media','Grande'), true)) { $erros[] = 'Tamanho inválido.'; }

    if (empty($erros)) {
        $preco = (float)$preco_esmalte;
        $estoque_min = (int)$estoque_minimo_esmalte;
        $stmt = $conn->prepare("UPDATE esmaltes SET nome = ?, cores = ?, preco = ?, categorias = ?, marcas = ?, estoque_minimo = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('ssdssii', $nome_esmalte, $cores_esmalte, $preco, $categorias_esmalte, $marcas_esmalte, $estoque_min, $esmalte_id);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = 'esmalte atualizada com sucesso.';
            } else {
                $_SESSION['flash_error'] = 'Erro ao atualizar esmalte: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['flash_error'] = 'Erro ao preparar atualização: ' . $conn->error;
        }
        header("Location: esmaltes.php");
        exit();
    } else {
        $_SESSION['flash_error'] = implode(' ', $erros);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Esmalte - Studio D.I.Y</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Editar Esmalte</h1>

        <form method="post">
            <div class="form-row">
                <div class="form-group">
                    <label>Nome:</label>
                    <input type="text" name="nome" required value="<?= htmlspecialchars($esmalte['nome']) ?>">
                </div>
                <div class="form-group">
                    <label>Preço (R$):</label>
                    <input type="number" name="preco" step="0.01" required value="<?= $esmalte['preco'] ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Categorias:</label>
                    <select name="tamanho" required>
                        <option value="Cremoso" <?= $esmalte['categorias'] == 'Cremoso' ? 'selected' : '' ?>>Cremoso</option>
                        <option value="Metalico" <?= $esmalte['categorias'] == 'Metalico' ? 'selected' : '' ?>>Metalico</option>
                        <option value="Glitter" <?= $esmalte['categorias'] == 'Glitter' ? 'selected' : '' ?>>Glitter</option>
                        <option value="Perolado" <?= $esmalte['categorias'] == 'Perolado' ? 'selected' : '' ?>>Perolado</option>
                        <option value="Fosco" <?= $esmalte['categorias'] == 'Fosco' ? 'selected' : '' ?>>Fosco</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Marcas:</label>
                    <input type="text" name="marcas" required value="<?= htmlspecialchars($esmalte['marcas']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Estoque Mínimo:</label>
                    <input type="number" name="estoque_minimo" min="0" required value="<?= $esmalte['estoque_minimo'] ?>">
                </div>
                <div class="form-group">
                    <label>Cores:</label>
                    <textarea name="cores" required><?= htmlspecialchars($esmalte['cores']) ?></textarea>
                </div>
            </div>
            <button type="submit" name="update" class="btn">Atualizar</button>
            <a href="pizzas.php" class="btn">Cancelar</a>
        </form>
    </div>
</body>
</html>


