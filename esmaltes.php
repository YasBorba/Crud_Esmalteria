
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
include 'funcoes_estoque.php';

// Fallback para app_log caso não esteja definido em config.php
if (!function_exists('app_log')) {
    function app_log($message) {
        $date = date('Y-m-d H:i:s');
        $line = "[$date] " . (is_string($message) ? $message : json_encode($message, JSON_UNESCAPED_UNICODE)) . PHP_EOL;
        error_log('[esmalteria] ' . $line);
    }
}

// Pegar o que o usuário digitou na busca
$texto_busca = $_GET['busca'] ?? '';

// Se o usuário clicou em "Cadastrar"
if (isset($_POST['add'])) {
    app_log(['route' => 'esmaltes_add', 'post' => $_POST]);
    $nome_esmalte = trim($_POST['nome'] ?? '');
    $cores_esmalte = trim($_POST['cores'] ?? '');
    $preco_esmalte = str_replace(',', '.', trim($_POST['preco'] ?? '0'));
    $categoria_esmalte = trim($_POST['categoria'] ?? '');
    $marca_esmalte = trim($_POST['marca'] ?? '');
    $estoque_minimo_esmalte = trim($_POST['estoque_minimo'] ?? '0');

    $erros = array();
    if ($nome_esmalte === '') { $erros[] = 'Informe o nome.'; }
    if ($cores_esmalte === '') { $erros[] = 'Informe as cores.'; }
    if ($categoria_esmalte === '') { $erros[] = 'Informe a categoria.'; }
    if ($marca_esmalte === '') { $erros[] = 'Informe a marca.'; }

    if (!is_numeric($preco_esmalte)) { $erros[] = 'Preço inválido.'; }
    if (!ctype_digit((string)$estoque_minimo_esmalte)) { $erros[] = 'Estoque mínimo inválido.'; }

    // Normalizar categoria para os valores aceitos no ENUM
    $map_categoria = array(
        'Cremoso' => 'Cremoso',
        'Metálico' => 'Metalico',
        'Glitter' => 'Glitter',
        'Perolado' => 'Perolado',
        'Fosco' => 'Fosco',
    );
    if (isset($map_categoria[$categoria_esmalte])) {
        $categoria_esmalte = $map_categoria[$categoria_esmalte];
    }
    if (!in_array($categoria_esmalte, array('Cremoso', 'Metalico', 'Glitter', 'Perolado', 'Fosco'), true)) {
        $erros[] = 'Categoria inválida.';
    }

    if (empty($erros)) {
        $preco = (float)$preco_esmalte;
        $estoque_min = (int)$estoque_minimo_esmalte;

        $stmt = $conn->prepare("INSERT INTO esmaltes (nome, cores, preco, categoria, marca, estoque_minimo, ativo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        if ($stmt) {
            $stmt->bind_param('ssdssi', $nome_esmalte, $cores_esmalte, $preco, $categoria_esmalte, $marca_esmalte, $estoque_min);
            if ($stmt->execute()) {
                $_SESSION['flash_success'] = 'Esmalte cadastrado com sucesso.';
                app_log(['route' => 'esmaltes_add', 'status' => 'ok', 'insert_id' => $conn->insert_id]);
            } else {
                $_SESSION['flash_error'] = 'Erro ao cadastrar esmalte: ' . $stmt->error;
                app_log(['route' => 'esmaltes_add', 'status' => 'fail', 'error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            $_SESSION['flash_error'] = 'Erro ao preparar inserção: ' . $conn->error;
            app_log(['route' => 'esmaltes_add', 'status' => 'prepare_fail', 'error' => $conn->error]);
        }
    } else {
        $_SESSION['flash_error'] = implode(' ', $erros);
        app_log(['route' => 'esmaltes_add', 'status' => 'validation_fail', 'errors' => $erros]);
    }

    header("Location: esmaltes.php");
    exit();
}

// Buscar esmaltes no banco
if ($texto_busca) {
    $sql = "SELECT * FROM esmaltes WHERE nome LIKE '%$texto_busca%' OR cores LIKE '%$texto_busca%' ORDER BY nome";
} else {
    $sql = "SELECT * FROM esmaltes ORDER BY nome";
}
$resultado_esmaltes = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar Catalogo - Studio D.I.Y</title>
  <style>
/* ======== RESET E BASE ======== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background: linear-gradient(135deg, #f9d5e5, #fcd5ce, #f8c8dc);
  min-height: 100vh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 15px;
  color: #444;
}

/* ======== CONTAINER PRINCIPAL ======== */
.container {
  background: #fff;
  padding: 45px 35px;
  border-radius: 25px;
  width: 100%;
  max-width: 1100px;
  box-shadow: 0 8px 25px rgba(214, 51, 108, 0.25);
  animation: fadeIn 0.8s ease-in-out;
  overflow: hidden;
}

/* ======== CABEÇALHO / TÍTULO ======== */
.container h1 {
  text-align: center;
  margin-bottom: 30px;
  color: #d6336c;
  font-weight: 700;
  letter-spacing: 1px;
  font-size: 2.2rem;
}

/* ======== FORM DE BUSCA ======== */
.busca {
  background: #fff5f9;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 15px;
  border: 1px solid #f8c8dc;
  box-shadow: 0 3px 10px rgba(214, 51, 108, 0.1);
}

.busca form {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
}

.busca input {
  flex: 1;
  min-width: 200px;
  padding: 10px;
  border-radius: 12px;
  border: 2px solid #f8c8dc;
  font-size: 14px;
  transition: 0.3s;
}

.busca input:focus {
  border-color: #d6336c;
  box-shadow: 0 0 8px rgba(214, 51, 108, 0.4);
}

/* ======== BOTÕES ======== */
.btn,
form button {
  background: linear-gradient(135deg, #d6336c, #f0569b);
  color: #fff;
  border: none;
  border-radius: 12px;
  padding: 12px 25px;
  font-weight: bold;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  text-align: center;
}

.btn:hover,
form button:hover {
  background: linear-gradient(135deg, #b81e53, #fc4999);
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(214, 51, 108, 0.4);
}

/* ======== ALERTAS ======== */
.alert {
  padding: 12px 15px;
  border-radius: 10px;
  margin-bottom: 20px;
  font-weight: 500;
}

.alert.success {
  background: #e6f4ea;
  border-left: 5px solid #4caf50;
  color: #2e7d32;
}

.alert.error {
  background: #fdecea;
  border-left: 5px solid #e53935;
  color: #c62828;
}

/* ======== TABELA ======== */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

table th {
  background: #d6336c;
  color: #fff;
  padding: 12px;
  text-align: left;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

table td {
  padding: 10px;
  border-bottom: 1px solid #f3b7c7;
  font-size: 14px;
}

table tr:nth-child(even) {
  background: #fff5f9;
}

table tr:hover {
  background: #ffe6ef;
  transition: 0.2s;
}

/* ======== STATUS DE ESTOQUE ======== */
.estoque-ok {
  background: #f1f8e9;
}

.estoque-baixo {
  background: #ffebee;
}

.status-indicator {
  display: inline-block;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  margin-right: 6px;
}

.status-ok {
  background: #4caf50;
}

.status-baixo {
  background: #e53935;
}

/* ======== FORMULÁRIO DE CADASTRO ======== */
.form-cadastro {
  background: #fff5f9;
  padding: 20px;
  border: 1px solid #f8c8dc;
  border-radius: 15px;
  margin-top: 25px;
  box-shadow: 0 3px 10px rgba(214, 51, 108, 0.1);
}

.form-cadastro h3 {
  color: #d6336c;
  margin-bottom: 15px;
  text-align: center;
  font-weight: 600;
}

.form-row {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  justify-content: space-between;
  margin-bottom: 15px;
}

.form-group {
  flex: 1 1 250px;
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 6px;
  font-weight: 600;
  color: #555;
  font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 10px;
  border-radius: 12px;
  border: 2px solid #f8c8dc;
  font-size: 14px;
  transition: 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #d6336c;
  box-shadow: 0 0 8px rgba(214, 51, 108, 0.4);
  outline: none;
}

textarea {
  resize: vertical;
  min-height: 60px;
}

/* ======== ANIMAÇÃO ======== */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ======== RESPONSIVIDADE ======== */
@media (max-width: 900px) {
  .container {
    padding: 30px 20px;
  }

  .container h1 {
    font-size: 1.7rem;
  }

  form,
  .form-row {
    flex-direction: column;
    align-items: stretch;
  }

  .btn,
  form button {
    width: 100%;
  }

  table {
    font-size: 13px;
  }

  table td,
  table th {
    padding: 8px;
  }
}

@media (max-width: 500px) {
  table {
    display: block;
    overflow-x: auto;
  }

  .form-group label {
    font-size: 0.9em;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    font-size: 13px;
  }
}

  </style>
<body>
  <div class="container">
        <h1>Gerenciar Esmaltes</h1>

        <?php if (!empty($_GET['debug'])): ?>
            <div class="alert">
                <strong>DEBUG</strong>
                <pre style="white-space:pre-wrap;overflow:auto;max-height:200px;">POST: <?php echo htmlspecialchars(print_r($_POST, true)); ?>
SESSION: <?php echo htmlspecialchars(print_r($_SESSION, true)); ?></pre>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert success"><?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert error"><?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>

        <div style="background: #f9f9f9; padding: 15px; margin-bottom: 15px; border: 1px solid #ddd;">
            <form method="get" style="display: flex; gap: 10px;">
                <input name="busca" placeholder="Buscar..." value="<?= htmlspecialchars($texto_busca) ?>" style="flex: 1; padding: 8px;">
                <button type="submit" class="btn">Buscar</button>
                <a href="index.php" class="btn">Voltar</a>
            </form>
        </div>

        <table>
            <tr><th>Nome</th><th>Cores</th><th>Preço</th><th>Categoria</th><th>Marca</th><th>Estoque</th><th>Ações</th></tr>
            <?php 
            // Mostrar cada esmalte na tabela
            while($esmalte = $resultado_esmaltes->fetch_assoc()): 
                $esmalte_id = $esmalte['id'];
                
                // Calcular estoque atual desta esmalte
                $sql_entradas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE esmalte_id = $esmalte_id AND tipo = 'entrada'";
                $entradas = $conn->query($sql_entradas)->fetch_assoc();
                $total_entradas = $entradas['total'] ? $entradas['total'] : 0;
                
                $sql_saidas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE esmalte_id = $esmalte_id AND tipo = 'saida'";
                $saidas = $conn->query($sql_saidas)->fetch_assoc();
                $total_saidas = $saidas['total'] ? $saidas['total'] : 0;
                
                $estoque_atual = $total_entradas - $total_saidas;
                $estoque_minimo = $esmalte['estoque_minimo'];
                
                // Verificar se estoque está baixo
                $estoque_baixo = $estoque_atual <= $estoque_minimo;
            ?>
                <tr class="<?= $estoque_baixo ? 'estoque-baixo' : 'estoque-ok' ?>">
                    <td><span class="status-indicator <?= $estoque_baixo ? 'status-baixo' : 'status-ok' ?>"></span><?= htmlspecialchars($esmalte['nome']) ?></td>
                    <td><?= htmlspecialchars($esmalte['cores']) ?></td>
                    <td>R$ <?= number_format($esmalte['preco'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($esmalte['categoria']) ?></td>
                    <td><?= htmlspecialchars($esmalte['marca']) ?></td>
                    <td><strong><?= $estoque_atual ?></strong>/<?= $estoque_minimo ?><?= $estoque_baixo ? '<br><small>⚠️ Baixo!</small>' : '' ?></td>
                    <td>
                        <a href="editar_esmalte.php?id=<?= $esmalte['id'] ?>" class="btn" style="padding: 3px 8px; font-size: 11px;">Editar</a>
                        <a href="deletar_esmalte.php?id=<?= $esmalte['id'] ?>" class="btn" style="padding: 3px 8px; font-size: 11px;" onclick="return confirm('Excluir?')">Excluir</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">
            <h3>Adicionar Esmalte</h3>
            <form method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nome:</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label>Preço:</label>
                        <input type="number" name="preco" step="0.01" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoria:</label>
                        <select name="categoria" required>
                            <option value="">Selecione</option>
                            <option value="Cremoso">Cremoso</option>
                            <option value="Metalico">Metálico</option>
                            <option value="Glitter">Glitter</option>
                            <option value="Perolado">Perolado</option>
                            <option value="Fosco">Fosco</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Marca:</label>
                        <input type="text" name="marca" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Estoque Mínimo:</label>
                        <input type="number" name="estoque_minimo" min="0" value="5" required>
                    </div>
                    <div class="form-group">
                        <label>Cores:</label>
                        <textarea name="cores" required></textarea>
                    </div>
                </div>
                <button type="submit" name="add" class="btn">Cadastrar</button>
            </form>
        </div>

        <div style="text-align: center; margin-top: 15px;">
            <a href="movimentacoes.php" class="btn">Movimentações</a>
        </div>
    </div>
</body>
</html>
