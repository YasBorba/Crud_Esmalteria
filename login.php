<?php
// Começar a sessão
session_start();

// Se já está logado, vai para o menu
if (isset($_SESSION['usuario'])) { 
    header('Location: index.php'); 
    exit(); 
}

// Variável para mensagem de erro
$mensagem_erro = "";

// Se o usuário clicou em "Entrar"
if ($_POST) {
    // Conectar no banco
    include 'config.php';
    
    // Pegar o que o usuário digitou
    $nome_digitado = $_POST['nome'];
    $senha_digitada = $_POST['senha'];
    
    // Procurar o usuário no banco
    $sql = "SELECT * FROM usuarios WHERE nome='$nome_digitado' AND senha='$senha_digitada'";
    $resultado = $conn->query($sql);
    
    // Se encontrou o usuário
    if ($resultado->num_rows > 0) {
        $dados_usuario = $resultado->fetch_assoc();
        $_SESSION['usuario'] = $dados_usuario;
        header("Location: index.php");
        exit();
    } else {
        $mensagem_erro = "Usuário ou senha inválidos.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Studio D.I.Y</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <h1>Studio D.I.Y</h1>
        <form method="post">
            <div class="form-group">
                <label>Usuário:</label>
                <input type="text" name="nome" required>
            </div>
            <div class="form-group">
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>
            <button type="submit">Entrar</button>
            <?php if ($mensagem_erro): ?>
                <div class="erro"><?= $mensagem_erro ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
