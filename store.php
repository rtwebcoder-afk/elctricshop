<?php
require_once 'connection.php'; // DB connection file

// Item Add Karne ka Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $name     = trim($_POST['item_name'] ?? '');
    $price    = filter_var($_POST['item_price'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $quantity = filter_var($_POST['item_quantity'], FILTER_VALIDATE_INT) ?: 0; // Quantity field
    $desc     = trim($_POST['item_description'] ?? '');

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO store_items (item_name, item_price, quantity, item_description) VALUES (:name, :price, :quantity, :desc)");
        $stmt->execute([
            ':name'     => $name, 
            ':price'    => $price, 
            ':quantity' => $quantity,
            ':desc'     => $desc
        ]);
        header("Location: store.php");
        exit;
    }
}

// Item Delete Karne ka Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM store_items WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: store.php");
    exit;
}

// Tamam items fetch karna
$items = $pdo->query("SELECT * FROM store_items ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Inventory - FS Solar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen p-4 md:p-8">

    <div class="max-w-6xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Store Inventory</h1>
                <p class="text-slate-500 text-sm">Naye items add karein ya majooda stock ko manage karein.</p>
            </div>
            <a href="index.php" class="bg-slate-700 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">
                ← Back to Main Form 
            </a>
        </div>

        <!-- Add Item Form -->
        <div class="bg-white p-6 rounded-2xl shadow-md border border-slate-200">
            <h2 class="text-lg font-bold text-slate-700 mb-4 border-b pb-2">Add New Item</h2>
            <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Name *</label>
                    <input type="text" name="item_name" required placeholder="e.g. Solar Cable 6mm" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Item Price (PKR)</label>
                    <input type="number" step="0.01" name="item_price" placeholder="0.00" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Quantity (Stock) *</label>
                    <input type="number" name="item_quantity" required min="0" placeholder="0" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Description</label>
                    <input type="text" name="item_description" placeholder="Short description..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="md:col-span-4 text-right">
                    <button type="submit" name="add_item" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2.5 rounded-lg transition shadow-md">
                        + Add Item to Store
                    </button>
                </div>
            </form>
        </div>

        <!-- Store Items List Table -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                All Items in Store (<?php echo count($items); ?>)
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-100 text-slate-600 uppercase text-xs font-semibold">
                            <th class="p-4">#ID</th>
                            <th class="p-4">Item Name</th>
                            <th class="p-4">Price</th>
                            <th class="p-4">Quantity / Stock</th>
                            <th class="p-4">Description</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-slate-700 text-sm">
                        <?php if(count($items) > 0): ?>
                            <?php foreach($items as $item): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-4 font-mono text-slate-400">#<?php echo $item['id']; ?></td>
                                <td class="p-4 font-bold text-slate-800"><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td class="p-4 font-semibold text-green-600">Rs. <?php echo number_format($item['item_price'], 2); ?></td>
                                <td class="p-4">
                                    <!-- Dynamic Stock Badges -->
                                    <?php 
                                        $qty = (int)($item['quantity'] ?? 0);
                                        if($qty <= 0): 
                                    ?>
                                        <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">Out of Stock (0)</span>
                                    <?php elseif($qty < 5): ?>
                                        <span class="bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-xs font-bold">Low Stock (<?php echo $qty; ?>)</span>
                                    <?php else: ?>
                                        <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-full text-xs font-bold"><?php echo $qty; ?> Pcs</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-slate-500"><?php echo htmlspecialchars($item['item_description'] ?? ''); ?></td>
                                <td class="p-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Edit Button -->
                                        <a href="edit_item.php?id=<?php echo $item['id']; ?>" 
                                           class="bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs px-3 py-1.5 rounded-md shadow transition flex items-center gap-1">
                                            ✏️ <span>Edit</span>
                                        </a>

                                        <!-- Delete Button -->
                                        <a href="store.php?delete=<?php echo $item['id']; ?>" 
                                           onclick="return confirm('Kya aap wakai is item ko delete karna chahte hain?')" 
                                           class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-1.5 rounded-md font-semibold text-xs transition flex items-center gap-1">
                                            🗑️ <span>Delete</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400">Abhi store me koi item nahi hai. Upar diye gaye form se naya item add karein.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>