
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

/* ====== BODY ====== */
body {
  background: linear-gradient(135deg, #f9d5e5, #fcd5ce, #f8c8dc);
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 30px;
}

/* ====== CONTAINER ====== */
.container {
  background: #fff;
  border-radius: 25px;
  padding: 40px 60px;
  width: 800px;
  max-width: 95%;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  animation: fadeIn 0.8s ease-in-out;
  text-align: center;
}

/* ====== TÍTULO ====== */
.container h1 {
  font-size: 30px;
  color: #d6336c;
  font-weight: 700;
  margin-bottom: 20px;
  letter-spacing: 1px;
}

/* ====== BOAS-VINDAS ====== */
.welcome {
  font-size: 18px;
  color: #444;
  margin-bottom: 25px;
  font-weight: 500;
}

/* ====== ALERTA DE ESTOQUE ====== */
.alert {
  background-color: #fff0f6;
  border-left: 6px solid #d6336c;
  padding: 15px 20px;
  border-radius: 12px;
  text-align: left;
  margin-bottom: 25px;
  box-shadow: 0 4px 10px rgba(214, 51, 108, 0.1);
}

.alert h3 {
  color: #c2185b;
  margin-bottom: 10px;
}

.alert ul {
  margin-left: 20px;
  color: #444;
  font-size: 15px;
}

.alert a {
  display: inline-block;
  margin-top: 10px;
  color: #d6336c;
  font-weight: 600;
  text-decoration: none;
  transition: 0.3s;
}

.alert a:hover {
  color: #b81e53;
}

/* ====== MENU GRID ====== */
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 25px;
  margin-top: 30px;
}

.menu-item {
  background-color: #fff5f9;
  border-radius: 15px;
  padding: 25px;
  text-decoration: none;
  color: #333;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.menu-item:hover {
  background-color: #ffd6e8;
  transform: translateY(-5px);
  box-shadow: 0 5px 12px rgba(214, 51, 108, 0.25);
}

.menu-item h3 {
  color: #d6336c;
  margin-bottom: 10px;
  font-size: 20px;
}

.menu-item p {
  font-size: 14px;
  color: #555;
}

/* ====== BOTÃO SAIR ====== */
.btn {
  display: inline-block;
  margin-top: 30px;
  background: #d6336c;
  color: #fff;
  padding: 12px 45px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: bold;
  transition: 0.3s;
}

.btn:hover {
  background: #b81e53;
  transform: scale(1.05);
  box-shadow: 0 4px 10px rgba(214, 51, 108, 0.4);
}

/* ====== ANIMAÇÃO ====== */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

/* ====== RESPONSIVIDADE ====== */
@media (max-width: 768px) {
  .container {
    padding: 25px 20px;
  }

  .menu-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .menu-item h3 {
    font-size: 18px;
  }

  .container h1 {
    font-size: 24px;
  }
}
</style>
</head>
<body>
    <div class="container">
        <h1>Sistema Studio D.I.Y</h1>        
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
