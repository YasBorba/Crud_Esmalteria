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

// Se o usuário clicou em "Registrar"
if ($_POST['add_movimento'] ?? false) {
    $esmalte_escolhido = $_POST['esmalte_id'];
    $tipo_movimento = $_POST['tipo'];
    $quantidade_movimento = $_POST['quantidade'];
    $observacoes_movimento = $_POST['observacoes'];
    $usuario_id = $_SESSION['usuario']['id'];
    
    // Inserir movimentação no banco
    $sql = "INSERT INTO movimentacoes (esmalte_id, usuario_id, data_hora, tipo, quantidade, observacoes) 
            VALUES ($esmalte_escolhido, $usuario_id, NOW(), '$tipo_movimento', $quantidade_movimento, '$observacoes_movimento')";
    $conn->query($sql);
    
    // Voltar para a mesma página
    header("Location: movimentacoes.php");
    exit();
}

// Buscar todos os esmaltes ativas
$sql_esmaltes = "SELECT * FROM esmaltes WHERE ativo = 1 ORDER BY nome";
$resultado_esmaltes = $conn->query($sql_esmaltes);

// Buscar últimas 20 movimentações
$sql_movimentacoes = "SELECT m.*, p.nome as esmalte_nome, u.nome as usuario_nome 
                      FROM movimentacoes m 
                      JOIN esmaltes p ON m.esmalte_id = p.id 
                      JOIN usuarios u ON m.usuario_id = u.id 
                      ORDER BY m.data_hora DESC LIMIT 20";
$resultado_movimentacoes = $conn->query($sql_movimentacoes);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações - Sistema Studio D.I.Y</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ====== RESET ====== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Poppins', sans-serif;
}

body {
  background: linear-gradient(135deg, #f9d5e5, #fcd5ce, #f8c8dc);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
}

/* ====== CONTAINER ====== */
.container {
  background: #fff;
  padding: 30px;
  border-radius: 20px;
  max-width: 1000px;
  width: 100%;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  animation: fadeIn 0.8s ease-in-out;
}

.container h1 {
  font-size: 28px;
  margin-bottom: 20px;
  color: #d6336c;
  font-weight: 700;
  text-align: center;
}

/* ====== BOTÃO ====== */
.btn {
      display: inline-block;
      padding: 12px 20px;
      background: #d6336c;
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      font-weight: bold;
      border-radius: 12px;
      transition: 0.3s;
      text-align: center;
    }

    .btn:hover {
      background: #b81e53;
      transform: scale(1.05);
      box-shadow: 0 4px 10px rgba(214, 51, 108, 0.4);
    }

    /* Centralizar botão voltar */
    .btn-voltar {
      display: flex;
      justify-content: center;
      margin: 15px 0;
      align-items: center;
    }

/* ====== FORM ====== */
form {
  margin-top: 15px;
}

.form-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 15px;
}

.form-group {
  flex: 1;
  min-width: 200px;
}

.form-group label {
  display: block;
  font-size: 14px;
  color: #333;
  margin-bottom: 6px;
  font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border-radius: 12px;
  border: 2px solid #f8c8dc;
  outline: none;
  font-size: 14px;
  transition: 0.3s;
  resize: none;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  border-color: #d6336c;
  box-shadow: 0 0 8px rgba(214, 51, 108, 0.4);
}

/* ====== TABELA ====== */
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

td.entrada {
  color: #1e7e34;
  font-weight: bold;
}

td.saida {
  color: #b81e53;
  font-weight: bold;
}

/* ====== ANIMAÇÃO ====== */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ====== RESPONSIVIDADE ====== */
@media (max-width: 768px) {
  .container {
    padding: 20px;
  }

  .form-row {
    flex-direction: column;
  }

  table th, table td {
    font-size: 12px;
    padding: 8px;
  }
}

    </style>
</head>
<body>
    <div class="container">
        <h1>Movimentações</h1>
        
        <a href="index.php" class="btn">Voltar</a>

        <div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin: 15px 0;">
            <h3>Nova Movimentação</h3>
            <form method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label>Esmalte:</label>
                        <select name="esmalte_id" required>
                            <option value="">Selecione um esmalte</option>
                            <?php while($esmalte = $resultado_esmaltes->fetch_assoc()): 
                                $esmalte_id = $esmalte['id'];
                                
                                // Calcular estoque atual deste esmalte
                                $sql_entradas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE esmalte_id = $esmalte_id AND tipo = 'entrada'";
                                $entradas = $conn->query($sql_entradas)->fetch_assoc();
                                $total_entradas = $entradas['total'] ? $entradas['total'] : 0;
                                
                                $sql_saidas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE esmalte_id = $esmalte_id AND tipo = 'saida'";
                                $saidas = $conn->query($sql_saidas)->fetch_assoc();
                                $total_saidas = $saidas['total'] ? $saidas['total'] : 0;
                                
                                $estoque_atual = $total_entradas - $total_saidas;
                            ?>
                                <option value="<?= $esmalte['id'] ?>">
                                    <?= htmlspecialchars($esmalte['nome']) ?> - <?= $estoque_atual ?>/<?= $esmalte['estoque_minimo'] ?> - R$ <?= number_format($esmalte['preco'], 2, ',', '.') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tipo:</label>
                        <select name="tipo" required>
                            <option value="">Selecione</option>
                            <option value="entrada">Entrada</option>
                            <option value="saida">Saída</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Quantidade:</label>
                        <input type="number" name="quantidade" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Observações:</label>
                        <textarea name="observacoes"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_movimento" class="btn">Registrar</button>
            </form>
        </div>

        <h3>Histórico Recente</h3>
        <table>
            <tr><th>Data/Hora</th><th>Esmalte</th><th>Usuário</th><th>Tipo</th><th>Qtd</th><th>Obs</th></tr>
            <?php while($movimentacao = $resultado_movimentacoes->fetch_assoc()): ?>
                <tr>
                    <td><?= date('d/m H:i', strtotime($movimentacao['data_hora'])) ?></td>
                    <td><?= htmlspecialchars($movimentacao['esmalte_nome']) ?></td>
                    <td><?= htmlspecialchars($movimentacao['usuario_nome']) ?></td>
                    <td class="<?= $movimentacao['tipo'] ?>"><?= $movimentacao['tipo'] == 'entrada' ? 'Entrada' : 'Saída' ?></td>
                    <td><?= $movimentacao['quantidade'] ?></td>
                    <td><?= htmlspecialchars($movimentacao['observacoes']) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        
        <div style="text-align: center; margin-top: 15px;">
            <a href="historico.php" class="btn">Histórico Completo</a>
        </div>
    </div>
</body>
</html>