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

// Pegar o que o usuário digitou na busca
$texto_busca = $_GET['busca'] ?? '';

// Se o usuário clicou em "Cadastrar"
if ($_POST['add'] ?? false) {
    $nome_esmalte = $_POST['nome'];
    $cores_esmalte = $_POST['cores'];
    $preco_esmalte = $_POST['preco'];
    $categorias_esmalte = $_POST['categorias'];
    $marcas_esmalte = $_POST['marca'];
    $estoque_minimo_esmalte = $_POST['estoque_minimo'];
    
    // Inserir nova pizza no banco
    $sql = "INSERT INTO pizzas (nome, cores, preco, categorias, marcas, estoque_minimo) 
            VALUES ('$nome_pizza', '$cores_pizza', $preco_pizza, '$categorias_pizza', '$marcas_pizza', $estoque_minimo_pizza)";
    $conn->query($sql);
    
    // Voltar para a mesma página
    header("Location: esmaltes.php");
    exit();
}

// Buscar pizzas no banco
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
        /* ===== RESET ===== */

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
  }

/* ===== BODY ===== */
body {
background: linear-gradient(135deg, #f9d5e5, #fcd5ce, #f8c8dc);
min-height: 100vh;
display: flex;
align-items: center;
justify-content: center;
padding: 20px;
color: #444;
}

/* ===== CONTAINER ===== */
.container {
background: #fff;
padding: 35px;
border-radius: 20px;
width: 100%;
max-width: 1100px;
box-shadow: 0 6px 20px rgba(0,0,0,0.15);
animation: fadeIn 0.8s ease-in-out;
}

.container h1 {
text-align: center;
margin-bottom: 25px;
color: #d6336c;
font-weight: 700;
letter-spacing: 1px;
}

/* ===== FORMULÁRIOS ===== */
form {
display: flex;
gap: 10px;
}

form input, form select, form textarea {
padding: 10px;
border: 1px solid #ddd;
border-radius: 8px;
flex: 1;
font-size: 14px;
}

form button, .btn {
padding: 10px 20px;
background: linear-gradient(135deg, #f06292, #f378afff);
color: #fff;
border: none;
border-radius: 10px;
font-weight: bold;
cursor: pointer;
transition: 0.3s ease;
text-decoration: none;
}

form button:hover, .btn:hover {
background: linear-gradient(135deg, #ec407a, #d64b82ff);
transform: scale(1.05);
box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* ===== TABELAS ===== */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

table th {
  background: #d6336c;
  color: #fff;
  padding: 12px;
  text-align: left;
  font-size: 14px;
}

table td {
  padding: 10px;
  border-bottom: 1px solid #f1b6c9;
  font-size: 14px;
}

table tr:nth-child(even) {
  background: #fdf0f5;
}

table tr:hover {
  background: #f8c8dc;
}

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

/* ===== FORMULÁRIO DE CADASTRO ===== */
.form-row {
display: flex;
gap: 15px;
margin-bottom: 15px;
}

.form-group {
flex: 1;
display: flex;
flex-direction: column;
}

.form-group label {
margin-bottom: 5px;
font-weight: 600;
color: #555;
}

/* ===== ANIMAÇÃO ===== */
@keyframes fadeIn {
from { opacity: 0; transform: translateY(-15px); }
to { opacity: 1; transform: translateY(0); }
}

/* ===== RESPONSIVIDADE ===== */
@media (max-width: 768px) {
.form-row {
flex-direction: column;
}
table {
font-size: 13px;
}
form {
flex-direction: column;
}
}

    </style>
<body>
    <div class="container">
        <h1>Gerenciar Catalogo</h1>

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
            // Mostrar cada pizza na tabela
            while($esmalte = $resultado_esmlates->fetch_assoc()): 
                $esmalte_id = $esmalte['id'];
                
                // Calcular estoque atual desta pizza
                $sql_entradas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE pizza_id = $esmalte_id AND tipo = 'entrada'";
                $entradas = $conn->query($sql_entradas)->fetch_assoc();
                $total_entradas = $entradas['total'] ? $entradas['total'] : 0;
                
                $sql_saidas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE pizza_id = $esmalte_id AND tipo = 'saida'";
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
                    <td><?= htmlspecialchars($esmalte['tamanho']) ?></td>
                    <td><?= htmlspecialchars($esmalte['categoria']) ?></td>
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
                            <option value="Metalico">Metalico</option>
                            <option value="Glitter">Glitter</option>
                            <option value="Perolado">Perolado</option>
                            <option value="Fosco">Fosco</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Marca:</label>
                        <input type="text" name="categoria" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Estoque Mínimo:</label>
                        <input type="number" name="estoque_minimo" min="0" value="5" required>
                    </div>
                    <div class="form-group">
                        <label>Cores:</label>
                        <textarea name="ingredientes" required></textarea>
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




