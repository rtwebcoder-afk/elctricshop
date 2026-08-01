<?php
require_once 'connection.php'; // Database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = trim($_POST['name'] ?? '');
    $date        = trim($_POST['date'] ?? '');
    $fault       = trim($_POST['fault'] ?? '');
    $accessories = trim($_POST['accessories'] ?? '');
    $note        = trim($_POST['note'] ?? '');
    $amount      = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $advance     = filter_var($_POST['advance'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $remaining   = $amount - $advance;

    if (!empty($name) && !empty($date) && !empty($fault)) {
        $sql = "INSERT INTO service_entries (customer_name, entry_date, fault, accessories, note, amount, advance, remaining) 
                VALUES (:name, :date, :fault, :accessories, :note, :amount, :advance, :remaining)";
        
        $stmt = $pdo->prepare($sql);
        $inserted = $stmt->execute([
            ':name'        => $name,
            ':date'        => $date,
            ':fault'       => $fault,
            ':accessories' => $accessories,
            ':note'        => $note,
            ':amount'      => $amount,
            ':advance'     => $advance,
            ':remaining'   => $remaining
        ]);

        if ($inserted) {
            $last_id = $pdo->lastInsertId();
            // Data save hone ke baad direct show.php par redirect ho raha hai
            header("Location: show.php?id=" . $last_id);
            exit;
        }
    } else {
        echo "Required fields missing!";
    }
} else {
    header("Location: index.php");
    exit;
}
?>