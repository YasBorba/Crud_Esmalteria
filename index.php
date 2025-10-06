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

// Verificar se tem estoque baixo
$alerta_estoque = gerarAlertaEstoque($conn);
?>

<!DOCTYPE html>

<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema Studio D.I.Y</title>
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
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  color: #444;
}

.container {
  background: #fff;
  padding: 35px;
  border-radius: 20px;
  width: 100%;
  max-width: 950px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  animation: fadeIn 0.8s ease-in-out;
}

.container h1 {
  text-align: center;
  margin-bottom: 25px;
  color: #d6336c;
  font-weight: 700;
  letter-spacing: 1px;
}

.welcome {
  background: linear-gradient(90deg, #f48fadff, #fe5691ff);
  padding: 12px;
  margin-bottom: 20px;
  text-align: center;
  color: white;
  border-radius: 12px;
  font-weight: 500;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.alert {
  background: #ffebee;
  padding: 20px;
  margin-bottom: 20px;
  border: 2px solid #ffcdd2;
  border-radius: 15px;
  color: #b81e53;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.alert h3 {
  margin-bottom: 10px;
  font-size: 18px;
}

.alert ul {
  margin: 10px 0 15px 20px;
}

.alert a {
  display: inline-block;
  margin-top: 8px;
  padding: 8px 15px;
  background: #d6336c;
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  transition: 0.3s;
}

.alert a:hover {
  background: #b81e53;
  transform: scale(1.05);
}

.menu-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 15px;
  margin-bottom: 25px;
  justify-content: center;
}

.menu-item {
  flex: 1;
  min-width: 220px;
  max-width: 280px;
  background: linear-gradient(135deg, #ec4079ff, #ff77a9ff);
  padding: 20px;
  border-radius: 15px;
  color: #fff;
  text-decoration: none;
  text-align: center;
  transition: 0.3s ease;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.menu-item h3 {
  margin-bottom: 8px;
  font-size: 20px;
  font-weight: bold;
}

.menu-item p {
  font-size: 14px;
  opacity: 0.9;
}

.menu-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.btn {
  padding: 10px 20px;
  background: linear-gradient(135deg, #f06292, #f378afff);
  color: #fff;
  border-radius: 10px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s ease;
  display: inline-block;
}

.btn:hover {
  background: linear-gradient(135deg, #ec407a, #aa246eff);
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .container {
    padding: 25px;
  }
  .menu-grid {
    flex-direction: column;
    align-items: center;
  }
}

  </style>
</head>
<body>
  <div class="container">
    <h1>Studio D.I.Y</h1>        
    <div class="welcome">
      Bem-vindo, <?= htmlspecialchars($_SESSION['usuario']['nome']) ?>!
    </div>


<?php if ($alerta_estoque): ?>
  <div class="alert">
    <h3>Estoque Baixo!</h3>
    <ul>
      <?php foreach ($alerta_estoque['esmaltes'] as $esmalte): ?>
        <li><?= htmlspecialchars($esmalte['nome']) ?> - <?= $esmalte['estoque_atual'] ?>/<?= $esmalte['estoque_minimo'] ?></li>
      <?php endforeach; ?>
    </ul>
    <p><a href="movimentacoes.php">Registrar Movimentações</a></p>
  </div>
<?php endif; ?>

<div class="menu-grid">
  <a href="esmaltes.php" class="menu-item">
    <h3>Esmaltes</h3>
    <p>Gerenciar catálogo</p>
  </a>
  <a href="movimentacoes.php" class="menu-item">
    <h3>Movimentações</h3>
    <p>Entrada e saída</p>
  </a>
  <a href="historico.php" class="menu-item">
    <h3>Histórico</h3>
    <p>Ver movimentações</p>
  </a>
</div>
<div style="text-align: center; margin-top: 20px;">
  <a href="logout.php" class="btn">Sair</a>
</div>

  </div>
</body>
</html>
