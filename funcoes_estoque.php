
<?php
// Função para pegar esmaltes com estoque baixo
function getEsmaltesEstoqueBaixo($conn) {
    // Buscar todas os esmaltes
    $sql = "SELECT * FROM esmaltes WHERE ativo = 1";
    $resultado = $conn->query($sql);
    
    $esmaltes_estoque_baixo = array();
    
    // Para cada esmalte, calcular o estoque
    while ($esmalte = $resultado->fetch_assoc()) {
        $esmalte_id = $esmalte['id'];
        
        // Somar todas as entradas
        $sql_entradas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE esmalte_id = $esmalte_id AND tipo = 'entrada'";
        $entradas = $conn->query($sql_entradas)->fetch_assoc();
        $total_entradas = $entradas['total'] ? $entradas['total'] : 0;
        
        // Somar todas as saídas
        $sql_saidas = "SELECT SUM(quantidade) as total FROM movimentacoes WHERE esmalte_id = $esmalte_id AND tipo = 'saida'";
        $saidas = $conn->query($sql_saidas)->fetch_assoc();
        $total_saidas = $saidas['total'] ? $saidas['total'] : 0;
        
        // Calcular estoque atual
        $estoque_atual = $total_entradas - $total_saidas;
        
        // Se estoque está baixo, adicionar na lista
        if ($estoque_atual <= $esmalte['estoque_minimo']) {
            $esmalte['estoque_atual'] = $estoque_atual;
            $esmaltes_estoque_baixo[] = $esmalte;
        }
    }
    
    return $esmaltes_estoque_baixo;
}

// Função para gerar alerta de estoque baixo
function gerarAlertaEstoque($conn) {
    $esmaltes_com_estoque_baixo = getEsmaltesEstoqueBaixo($conn);
    
    // Se não tem esmaltes com estoque baixo
    if (empty($esmaltes_com_estoque_baixo)) {
        return null;
    }
    
    // Se tem esmaltes com estoque baixo, criar o alerta
    $quantidade_esmaltes = count($esmaltes_com_estoque_baixo);
    
    return array(
        'quantidade' => $quantidade_esmaltes,
        'esmaltes' => $esmaltes_com_estoque_baixo,
        'mensagem' => $quantidade_esmaltes . " esmalte(s) com estoque baixo!"
    );
}
?>
