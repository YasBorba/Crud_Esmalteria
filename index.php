
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
    <link rel="stylesheet" href="style.css">
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
