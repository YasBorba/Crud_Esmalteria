
<?php
// Inicia a sessão para verificar se o usuário está autenticado
session_start();

// Se não houver usuário logado, redireciona para a página de login
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Inclui o arquivo de configuração para conexão com o banco de dados
include 'config.php';

// Recebe o ID do esmalte via GET
$id = $_GET['id'] ?? 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM movimentacoes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    // Prepara uma consulta segura para deletar o esmalte
    $stmt = $conn->prepare("DELETE FROM esmaltes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Redireciona para a lista de esmaltes
header("Location: esmaltes.php");
exit();
?>
