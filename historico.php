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

// Pegar os filtros que o usuário escolheu
$filtro_esmalte = $_GET['esmalte'] ?? '';
$filtro_tipo = $_GET['tipo'] ?? '';

// Montar a consulta SQL baseada nos filtros
$sql = "SELECT m.id, p.nome AS esmalte_nome, u.nome AS usuario_nome, m.tipo, m.quantidade, m.data_hora, m.observacoes 
        FROM movimentacoes m
        JOIN esmaltes p ON m.esmalte_id = p.id
        JOIN usuarios u ON m.usuario_id = u.id
        WHERE 1=1";

// Se escolheu um esmalte específico
if ($filtro_esmalte) {
    $sql .= " AND m.esmalte_id = $filtro_esmalte";
}

// Se escolheu um tipo específico
if ($filtro_tipo) {
    $sql .= " AND m.tipo = '$filtro_tipo'";
}

$sql .= " ORDER BY m.data_hora DESC";

// Executar a consulta
$resultado_movimentacoes = $conn->query($sql);

// Buscar todos os esmaltes para o filtro
$sql_esmaltes = "SELECT * FROM esmaltes ORDER BY nome";
$resultado_esmaltes = $conn->query($sql_esmaltes);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Histórico - Sistema Studio D.I.Y</title>

  <style>
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
      box-shadow: 0 6px 20px rgba(255, 99, 162, 0.56);
      animation: fadeIn 0.8s ease-in-out;
    }

    .container h1 {
      font-size: 28px;
      margin-bottom: 20px;
      color: #d6336c;
      font-weight: 700;
      text-align: center;
    }

    h3 {
      color: #d6336c;
    }

    /* ====== BOTÕES ====== */
    .btn {
      display: inline-block;
      padding: 12px 20px;
      background: linear-gradient(135deg, #d6336c, #f0569bff);
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      font-weight: bold;
      border-radius: 12px;
      transition: 0.3s;
      border: none;
      cursor: pointer;
    }

    .btn:hover {
      background: linear-gradient(135deg, #b81e53, #fc4999ff);
      transform: scale(1.05);
      box-shadow: 0 4px 10px rgba(214, 51, 108, 0.4);
    }

    /* Centralizar botão voltar */
    .btn-voltar {
      display: flex;
      justify-content: center;
      margin: 15px 0;
    }

    /* ====== FILTROS ====== */
    .filter-box {
      background: #fff0f5;
      border: 2px solid #f8c8dc;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 25px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .filter-box h3 {
      margin-bottom: 15px;
      font-size: 20px;
      color: #d6336c;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .form-row {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
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

    .form-group select {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      border: 2px solid #f8c8dc;
      outline: none;
      font-size: 14px;
      transition: 0.3s;
    }

    .form-group select:focus {
      border-color: #d6336c;
      box-shadow: 0 0 8px rgba(214, 51, 108, 0.4);
    }

    /* ====== TABELA ====== */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    table th {
      background: #d6336c;
      color: #fff;
      padding: 12px;
      text-align: center;
      font-size: 14px;
    }

    table td {
      padding: 10px;
      border-bottom: 1px solid #f1b6c9;
      font-size: 14px;
      text-align: center;
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

      .btn {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Histórico</h1>

    <div class="btn-voltar">
      <a href="index.php" class="btn">Voltar</a>
    </div>

    <div class="filter-box">
      <h3>Filtros</h3>
      <form method="get">
        <div class="form-row">
          <div class="form-group">
            <label>Esmalte:</label>
            <select name="esmalte">
              <option value="">Todas</option>
              <?php while($esmalte = $resultado_esmaltes->fetch_assoc()): ?>
                <option value="<?= $esmalte['id'] ?>" <?= $filtro_esmalte == $esmalte['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($esmalte['nome']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Tipo:</label>
            <select name="tipo">
              <option value="">Todos</option>
              <option value="entrada" <?= $filtro_tipo == 'entrada' ? 'selected' : '' ?>>Entrada</option>
              <option value="saida" <?= $filtro_tipo == 'saida' ? 'selected' : '' ?>>Saída</option>
            </select>
          </div>
        </div>

        <div class="form-row" style="justify-content: center;">
          <button type="submit" class="btn">Filtrar</button>
          <a href="historico.php" class="btn">Limpar</a>
        </div>
      </form>
    </div>

    <table>
      <tr>
        <th>ID</th>
        <th>Data/Hora</th>
        <th>Esmalte</th>
        <th>Usuário</th>
        <th>Tipo</th>
        <th>Qtd</th>
        <th>Obs</th>
      </tr>

      <?php 
      if ($resultado_movimentacoes->num_rows > 0): 
        while($movimentacao = $resultado_movimentacoes->fetch_assoc()): 
      ?>
        <tr>
          <td><?= $movimentacao['id'] ?></td>
          <td><?= date('d/m H:i', strtotime($movimentacao['data_hora'])) ?></td>
          <td><?= htmlspecialchars($movimentacao['esmalte_nome']) ?></td>
          <td><?= htmlspecialchars($movimentacao['usuario_nome']) ?></td>
          <td class="<?= $movimentacao['tipo'] ?>"><?= $movimentacao['tipo'] == 'entrada' ? 'Entrada' : 'Saída' ?></td>
          <td><?= $movimentacao['quantidade'] ?></td>
          <td><?= htmlspecialchars($movimentacao['observacoes']) ?></td>
        </tr>
      <?php 
        endwhile; 
      else: 
      ?>
        <tr>
          <td colspan="7" style="text-align: center; padding: 20px;">Nenhuma movimentação encontrada.</td>
        </tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>