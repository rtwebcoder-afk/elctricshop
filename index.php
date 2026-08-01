<?php
require_once 'connection.php'; // Database connection file

$message = "";

// 1. Store se tamam items fetch karna
$store_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM store_items ORDER BY item_name ASC");
    $store_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handling error if table missing
}

// 2. Form Submit hone par Data Save aur Stock Deduct karna
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = htmlspecialchars(trim($_POST['name'] ?? ''));
    $date  = htmlspecialchars(trim($_POST['date'] ?? ''));
    $fault = htmlspecialchars(trim($_POST['fault'] ?? ''));
    
    // Process Selected Accessories with Quantities
    $selected_items = $_POST['items'] ?? []; 
    $quantities     = $_POST['qty'] ?? [];   

    $accessories_formatted_list = [];
    $items_to_deduct = [];

    if (is_array($selected_items)) {
        foreach ($selected_items as $item_id) {
            $qty = filter_var($quantities[$item_id] ?? 1, FILTER_VALIDATE_INT) ?: 1;

            // Fetch Item Name & Check Current Stock
            $stmt_item = $pdo->prepare("SELECT item_name, quantity FROM store_items WHERE id = :id");
            $stmt_item->execute([':id' => $item_id]);
            $fetched_item = $stmt_item->fetch(PDO::FETCH_ASSOC);

            if ($fetched_item) {
                $item_name = $fetched_item['item_name'];
                $accessories_formatted_list[] = $item_name . " (x" . $qty . ")";
                
                $items_to_deduct[] = [
                    'id'  => $item_id,
                    'qty' => $qty
                ];
            }
        }
    }

    $accessories = htmlspecialchars(implode(', ', $accessories_formatted_list));

    $note      = htmlspecialchars(trim($_POST['note'] ?? ''));
    $amount    = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $advance   = filter_var($_POST['advance'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $remaining = $amount - $advance;

    if (!empty($name) && !empty($date) && !empty($fault)) {
        try {
            // Transaction Start (Database Safety)
            $pdo->beginTransaction();

            // 1. Insert into service_entries
            $sql = "INSERT INTO service_entries (customer_name, entry_date, fault, accessories, note, amount, advance, remaining) 
                    VALUES (:name, :date, :fault, :accessories, :note, :amount, :advance, :remaining)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'        => $name, 
                ':date'        => $date, 
                ':fault'       => $fault, 
                ':accessories' => $accessories, 
                ':note'        => $note, 
                ':amount'      => $amount, 
                ':advance'     => $advance, 
                ':remaining'   => $remaining
            ]);

            $last_id = $pdo->lastInsertId();

            // 2. Deduct Stock from store_items
            if (!empty($items_to_deduct)) {
                $update_stock_sql = "UPDATE store_items SET quantity = GREATEST(0, quantity - :qty) WHERE id = :id";
                $stmt_stock = $pdo->prepare($update_stock_sql);

                foreach ($items_to_deduct as $deduct) {
                    $stmt_stock->execute([
                        ':qty' => $deduct['qty'],
                        ':id'  => $deduct['id']
                    ]);
                }
            }

            // Commit Transaction
            $pdo->commit();

            // Direct show.php par redirect
            header("Location: show.php?id=" . $last_id);
            exit;

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = "Error: Record save nahi ho saka! (" . $e->getMessage() . ")";
        }
    } else {
        $message = "Barah-e-karam tamam zaroori fields (Name, Date, Fault) pur karein!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FS Solar - Service Form</title>
    
    <link rel="icon" type="image/jpeg" href="logo.jpeg">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Quantity Plus/Minus Controls
        function changeQty(itemId, change) {
            const qtyInput = document.getElementById('qty_' + itemId);
            const checkbox = document.getElementById('item_check_' + itemId);
            if (!qtyInput) return;

            let currentVal = parseInt(qtyInput.value) || 1;
            let maxVal = parseInt(qtyInput.getAttribute('max')) || 9999;
            let newVal = currentVal + change;

            if (newVal >= 1 && newVal <= maxVal) {
                qtyInput.value = newVal;
                
                // Item select na ho to auto-check kar dein
                if (checkbox && !checkbox.checked) {
                    checkbox.checked = true;
                }
                
                calculateAccessoriesTotal();
            }
        }

        // Accessories ki price (Price x Quantity) Calculate karne ka function
        function calculateAccessoriesTotal() {
            let totalAccessoriesPrice = 0;
            const checkboxes = document.querySelectorAll('.item-checkbox:checked');

            checkboxes.forEach(cb => {
                const itemId = cb.value;
                const price = parseFloat(cb.getAttribute('data-price')) || 0;
                const qtyInput = document.getElementById('qty_' + itemId);
                const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;

                totalAccessoriesPrice += (price * qty);
            });

            // Set Total Amount Field
            document.getElementById('amount').value = totalAccessoriesPrice.toFixed(2);
            
            // Auto update remaining calculation
            calculateRemaining();
        }

        // Automatic Remaining Amount Calculate karne ka function
        function calculateRemaining() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const advance = parseFloat(document.getElementById('advance').value) || 0;
            const remaining = amount - advance;
            document.getElementById('remaining').value = (remaining >= 0 ? remaining : 0).toFixed(2);
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex items-center justify-center">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        
        <!-- Header Section with Logo & Store Button -->
        <div class="bg-gradient-to-r from-blue-50 to-orange-50 p-6 border-b border-slate-200 relative">
            <div class="grid grid-cols-3 items-center mb-2">
                <div></div>
                <div class="flex justify-center">
                    <img src="logo.jpeg" alt="FS Solar Logo" class="h-24 object-contain" onError="this.onerror=null; this.src='https://via.placeholder.com/200x80?text=FS+Solar';" />
                </div>
                <div class="flex justify-end">
                    <a href="store.php" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold py-2 px-3 rounded-lg shadow transition flex items-center gap-1">
                        🛒 <span>Manage Store</span>
                    </a>
                </div>
            </div>
            <p class="text-xs text-center tracking-wider text-slate-500 uppercase font-semibold">Energy & Security Solution</p>
        </div>

        <!-- Notification / Warning Message -->
        <?php if (!empty($message)): ?>
            <div class="m-6 mb-0 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-center font-medium">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Form Section -->
        <form action="" method="POST" class="p-6 md:p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Name Field -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Customer Name</label>
                    <input type="text" id="name" name="name" required placeholder="Enter customer name" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <!-- Date Field -->
                <div>
                    <label for="date" class="block text-sm font-semibold text-slate-700 mb-1">Date</label>
                    <input type="date" id="date" name="date" required 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <!-- Fault Field -->
            <div>
                <label for="fault" class="block text-sm font-semibold text-slate-700 mb-1">Fault</label>
                <textarea id="fault" name="fault" rows="2" required placeholder="Enter the fault or issue description"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></textarea>
            </div>

            <!-- Accessories Section with Quantity Selector -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-slate-700">Select Accessories & Quantities</label>
                    <span class="text-xs text-slate-400">Items select karein aur quantity set karein</span>
                </div>

                <div class="max-h-60 overflow-y-auto border border-slate-300 rounded-lg divide-y divide-slate-100 bg-slate-50/50 p-2">
                    <?php if (!empty($store_items)): ?>
                        <?php foreach ($store_items as $item): 
                            $stock = (int)($item['quantity'] ?? 0);
                            $isOutOfStock = $stock <= 0;
                        ?>
                            <div class="flex items-center justify-between p-2 rounded-lg hover:bg-white transition <?php echo $isOutOfStock ? 'opacity-50' : ''; ?>">
                                
                                <!-- Checkbox & Details -->
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" 
                                           name="items[]" 
                                           id="item_check_<?php echo $item['id']; ?>" 
                                           value="<?php echo $item['id']; ?>" 
                                           data-price="<?php echo $item['item_price']; ?>"
                                           onchange="calculateAccessoriesTotal()"
                                           <?php echo $isOutOfStock ? 'disabled' : ''; ?>
                                           class="item-checkbox w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                    
                                    <div>
                                        <label for="item_check_<?php echo $item['id']; ?>" class="text-sm font-semibold text-slate-800 cursor-pointer">
                                            <?php echo htmlspecialchars($item['item_name']); ?>
                                        </label>
                                        <div class="text-xs text-slate-500">
                                            Rs. <?php echo number_format($item['item_price'], 2); ?> 
                                            <span class="ml-1 text-[11px] font-bold <?php echo $isOutOfStock ? 'text-red-500' : 'text-slate-400'; ?>">
                                                (Stock: <?php echo $stock; ?>)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Controls (+ / -) -->
                                <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-md p-1">
                                    <button type="button" 
                                            onclick="changeQty(<?php echo $item['id']; ?>, -1)" 
                                            <?php echo $isOutOfStock ? 'disabled' : ''; ?>
                                            class="w-6 h-6 flex items-center justify-center bg-slate-100 text-slate-700 rounded hover:bg-slate-200 font-bold text-sm select-none">
                                        -
                                    </button>

                                    <input type="number" 
                                           name="qty[<?php echo $item['id']; ?>]" 
                                           id="qty_<?php echo $item['id']; ?>" 
                                           value="1" 
                                           min="1" 
                                           max="<?php echo $stock > 0 ? $stock : 1; ?>"
                                           oninput="calculateAccessoriesTotal()"
                                           <?php echo $isOutOfStock ? 'disabled' : ''; ?>
                                           class="w-12 text-center text-sm font-bold text-slate-800 outline-none">

                                    <button type="button" 
                                            onclick="changeQty(<?php echo $item['id']; ?>, 1)" 
                                            <?php echo $isOutOfStock ? 'disabled' : ''; ?>
                                            class="w-6 h-6 flex items-center justify-center bg-slate-100 text-slate-700 rounded hover:bg-slate-200 font-bold text-sm select-none">
                                        +
                                    </button>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-xs text-slate-400">Store me koi items nahi hain. Store page se add karein.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Note Field -->
            <div>
                <label for="note" class="block text-sm font-semibold text-slate-700 mb-1">Note </label>
                <textarea id="note" name="note" rows="2" placeholder="Enter any additional notes"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></textarea>
            </div>

            <!-- Payment Details (3 Grid Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <!-- Total Amount -->
                <div>
                    <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1">Total Amount</label>
                    <input type="number" step="0.01" id="amount" name="amount" oninput="calculateRemaining()" placeholder="0.00" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                </div>

                <!-- Advance Amount -->
                <div>
                    <label for="advance" class="block text-sm font-semibold text-slate-700 mb-1">Advance Amount</label>
                    <input type="number" step="0.01" id="advance" name="advance" oninput="calculateRemaining()" placeholder="0.00" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                </div>

                <!-- Remaining Amount -->
                <div>
                    <label for="remaining" class="block text-sm font-semibold text-slate-700 mb-1">Remaining Amount</label>
                    <input type="number" step="0.01" id="remaining" name="remaining" readonly placeholder="0.00" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-100 text-slate-600 font-bold outline-none cursor-not-allowed">
                </div>
            </div>

            <!-- Signature & Contact Section -->
            <div class="pt-6 border-t border-slate-200 flex justify-between items-end">
                <!-- Contact Number (Left) -->
                <div class="w-48 text-center">
                    <div class="border-b-2 border-slate-400 mb-1 h-12 flex items-end justify-center pb-1">
                        <span class="text-sm font-medium text-slate-800">0313 9158294</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-600 uppercase">Contact Number</span>
                </div>

                <!-- Authorized Signature (Right) -->
                <div class="w-48 text-center">
                    <div class="border-b-2 border-slate-400 mb-1 h-12"></div>
                    <span class="text-xs font-semibold text-slate-600 uppercase">Authorized Signature</span>
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-amber-500 hover:from-blue-700 hover:to-amber-600 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    Submit Entry
                </button>
            </div>

        </form>
    </div>

</body>
</html>