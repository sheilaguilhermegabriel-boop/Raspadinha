<?php
session_start();
require 'db.php'; // Conexão com MySQL

// Simulação de usuário logado (apenas teste)
$user_id = 1;

// Função para pegar saldo
function getBalance($conn, $user_id){
    $sql = "SELECT SUM(CASE WHEN type='deposit' THEN amount ELSE -amount END) as balance FROM transactions WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['balance'] ?? 0;
}

// Processar depósito
if(isset($_POST['deposit'])){
    $amount = floatval($_POST['amount']);
    $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,status) VALUES (?,?,?,?)");
    $status = 'pending';
    $type = 'deposit';
    $stmt->bind_param("isds",$user_id,$type,$amount,$status);
    $stmt->execute();
    echo "<p>Depósito de R$ $amount registrado. Status: pending</p>";
}

// Processar saque PIX
if(isset($_POST['withdraw'])){
    $amount = floatval($_POST['amount']);
    $balance = getBalance($conn, $user_id);
    if($amount > $balance){
        echo "<p>Saldo insuficiente!</p>";
    } else {
        $stmt = $conn->prepare("INSERT INTO transactions (user_id,type,amount,status) VALUES (?,?,?,?)");
        $status = 'pending';
        $type = 'withdraw';
        $stmt->bind_param("isds",$user_id,$type,$amount,$status);
        $stmt->execute();
        echo "<p>Saque de R$ $amount registrado. Status: pending</p>";
    }
}

// Pegar saldo
$balance = getBalance($conn, $user_id);

// Pegar histórico
$history = $conn->query("SELECT * FROM transactions WHERE user_id=$user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mini Carteira</title>
</head>
<body>
    <h1>Carteira do Usuário</h1>
    <p>Saldo atual: R$ <?php echo number_format($balance,2); ?></p>

    <h2>Depositar</h2>
    <form method="POST">
        <input type="number" step="0.01" name="amount" placeholder="Valor R$" required>
        <button type="submit" name="deposit">Depositar</button>
    </form>

    <h2>Sacar via PIX</h2>
    <form method="POST">
        <input type="number" step="0.01" name="amount" placeholder="Valor R$" required>
        <button type="submit" name="withdraw">Sacar</button>
    </form>

    <h2>Histórico</h2>
    <table border="1" cellpadding="5">
        <tr><th>ID</th><th>Tipo</th><th>Valor</th><th>Status</th><th>Data</th></tr>
        <?php while($row = $history->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['type']; ?></td>
            <td><?php echo number_format($row['amount'],2); ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['created_at']; ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

