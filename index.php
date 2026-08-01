<?php
require_once 'connection.php'; // Database connection file

$message = "";

// 1. Store se tamam items fetch karna (Accessories Dropdown ke liye)
$store_items = [];
try {
    $stmt = $pdo->query("SELECT * FROM store_items ORDER BY item_name ASC");
    $store_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Agar store_items table na bana ho to error handle karein
}

// 2. Form Submit hone par Data Save aur Redirect karna
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = htmlspecialchars(trim($_POST['name'] ?? ''));
    $date  = htmlspecialchars(trim($_POST['date'] ?? ''));
    $fault = htmlspecialchars(trim($_POST['fault'] ?? ''));
    
    // Multiple Selected Accessories ko Combine karna (e.g., "Wire, Breaker, Frame")
    $accessories_array = $_POST['accessories'] ?? [];
    if (is_array($accessories_array)) {
        $accessories = htmlspecialchars(implode(', ', $accessories_array));
    } else {
        $accessories = htmlspecialchars($accessories_array);
    }

    $note      = htmlspecialchars(trim($_POST['note'] ?? ''));
    $amount    = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $advance   = filter_var($_POST['advance'], FILTER_VALIDATE_FLOAT) ?: 0.00;
    $remaining = $amount - $advance;

    if (!empty($name) && !empty($date) && !empty($fault)) {
        // Database me Insert SQL Query
        $sql = "INSERT INTO service_entries (customer_name, entry_date, fault, accessories, note, amount, advance, remaining) 
                VALUES (:name, :date, :fault, :accessories, :note, :amount, :advance, :remaining)";
        
        $stmt = $pdo->prepare($sql);
        $saved = $stmt->execute([
            ':name'        => $name, 
            ':date'        => $date, 
            ':fault'       => $fault, 
            ':accessories' => $accessories, 
            ':note'        => $note, 
            ':amount'      => $amount, 
            ':advance'     => $advance, 
            ':remaining'   => $remaining
        ]);

        if ($saved) {
            // Nayi save hui entry ka ID haasil karein
            $last_id = $pdo->lastInsertId();

            // Direct show.php par redirect karein ID ke sath
            header("Location: show.php?id=" . $last_id);
            exit;
        } else {
            $message = "Error: Record save nahi ho saka!";
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
        // Accessories ki price calculate karke Total Amount me add karne ka function
        function calculateAccessoriesTotal() {
            const selectElement = document.getElementById('accessories');
            let totalAccessoriesPrice = 0;

            // Handlers for selected options
            for (let option of selectElement.selectedOptions) {
                const price = parseFloat(option.getAttribute('data-price')) || 0;
                totalAccessoriesPrice += price;
            }

            // Set Total Amount Field
            document.getElementById('amount').value = totalAccessoriesPrice.toFixed(2);
            
            // Auto update remaining calculation
            calculateRemaining();
        }

        // Automatic Remaining Amount Calculate karne ke liye JavaScript function
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
            <!-- Left Column (Empty balance spacer) -->
            <div></div>

            <!-- Center Logo -->
            <div class="flex justify-center">
                <img src="logo.jpeg" alt="FS Solar Logo" class="h-24 object-contain" onError="this.onerror=null; this.src='https://via.placeholder.com/200x80?text=FS+Solar';" />
            </div>

            <!-- Right Column (Store Navigation Button) -->
            <div class="flex justify-end">
                <a href="store.php" class="bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold py-2 px-3 rounded-lg shadow transition flex items-center gap-1">
                    🛒 <span>Manage Store</span>
                </a>
            </div>
        </div>

        <!-- Tagline -->
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

            <!-- Accessories Field (Dynamic Dropdown from Database) -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="accessories" class="block text-sm font-semibold text-slate-700">Accessories</label>
                    <span class="text-xs text-slate-400">Ctrl / Cmd daba kar ek se zayada chun sakte hain</span>
                </div>
                <!-- Added onchange event for Auto Calculation -->
                <select id="accessories" name="accessories[]" multiple size="4" onchange="calculateAccessoriesTotal()" 
                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                    <?php if (!empty($store_items)): ?>
                        <?php foreach ($store_items as $item): ?>
                            <!-- data-price attribute pass kiya gaya hai JS calculation ke liye -->
                            <option value="<?php echo htmlspecialchars($item['item_name']); ?>" data-price="<?php echo htmlspecialchars($item['item_price']); ?>" class="py-1 px-2 border-b border-slate-100">
                                <?php echo htmlspecialchars($item['item_name']); ?> (Rs. <?php echo number_format($item['item_price'], 2); ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled class="text-slate-400">Store me koi items nahi hain. Store page se add karein.</option>
                    <?php endif; ?>
                </select>
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