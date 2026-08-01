<?php
require_once 'connection.php'; // DB Connection

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: store.php");
    exit;
}

// Item fetch karein
$stmt = $pdo->prepare("SELECT * FROM store_items WHERE id = :id");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: store.php");
    exit;
}

// Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_item'])) {
    $name     = trim($_POST['item_name'] ?? '');
    $price    = filter_var($_POST['item_price'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $quantity = filter_var($_POST['item_quantity'], FILTER_VALIDATE_INT) ?? 0; // Quantity value handle karna
    $desc     = trim($_POST['item_description'] ?? '');

    if (!empty($name)) {
        // SQL query me quantity column update karne ke liye add kar diya hai
        $stmt = $pdo->prepare("UPDATE store_items SET item_name = :name, item_price = :price, quantity = :quantity, item_description = :desc WHERE id = :id");
        $stmt->execute([
            ':name'     => $name,
            ':price'    => $price,
            ':quantity' => $quantity,
            ':desc'     => $desc,
            ':id'       => $id
        ]);
        header("Location: store.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - FS Solar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-4 md:p-8 flex items-center justify-center">

    <div class="max-w-xl w-full bg-white p-6 rounded-2xl shadow-md border border-slate-200">
        <div class="flex justify-between items-center mb-6 border-b pb-3">
            <h1 class="text-xl font-bold text-slate-800">Edit Store Item #<?php echo htmlspecialchars($item['id']); ?></h1>
            <a href="store.php" class="text-xs font-bold bg-slate-200 text-slate-700 px-3 py-1.5 rounded-lg hover:bg-slate-300 transition">← Cancel</a>
        </div>

        <form action="" method="POST" class="space-y-4">
            <!-- Item Name -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Name *</label>
                <input type="text" name="item_name" required value="<?php echo htmlspecialchars($item['item_name']); ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <!-- Item Price & Quantity Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Price (PKR)</label>
                    <input type="number" step="0.01" name="item_price" value="<?php echo htmlspecialchars($item['item_price']); ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Quantity (Stock) *</label>
                    <input type="number" name="item_quantity" min="0" required value="<?php echo htmlspecialchars($item['quantity'] ?? 0); ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
                <input type="text" name="item_description" value="<?php echo htmlspecialchars($item['item_description'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" name="update_item" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2.5 rounded-lg transition shadow-md">
                    Update Item Details
                </button>
            </div>
        </form>
    </div>

</body>
</html>