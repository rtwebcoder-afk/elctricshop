<?php
require_once 'connection.php'; // Aapki database connection file

$message = "";
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

// 1. Existing Record Fetch Karna
$stmt = $pdo->prepare("SELECT * FROM service_entries WHERE id = :id");
$stmt->execute([':id' => $id]);
$entry = $stmt->fetch();

if (!$entry) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Record nahi mila!</h2><a href='index.php'>Wapas Main Page Par Jayein</a></div>");
}

// 2. Form Update Form Logic
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
        $sql = "UPDATE service_entries 
                SET customer_name = :name, 
                    entry_date = :date, 
                    fault = :fault, 
                    accessories = :accessories, 
                    note = :note, 
                    amount = :amount, 
                    advance = :advance, 
                    remaining = :remaining 
                WHERE id = :id";
        
        $update_stmt = $pdo->prepare($sql);
        $updated = $update_stmt->execute([
            ':name'        => $name,
            ':date'        => $date,
            ':fault'       => $fault,
            ':accessories' => $accessories,
            ':note'        => $note,
            ':amount'      => $amount,
            ':advance'     => $advance,
            ':remaining'   => $remaining,
            ':id'          => $id
        ]);

        if ($updated) {
            // Update hone ke baad wapas show.php par redirect
            header("Location: show.php?id=" . $id);
            exit;
        }
    } else {
        $message = "Barah-e-karam lazmi fields (Name, Date, Fault) pur karein!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Receipt #<?php echo $entry['id']; ?> - FS Solar</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Automatic Remaining Amount Calculate karne ke liye JS function
        function calculateRemaining() {
            const amount = parseFloat(document.getElementById('amount').value) || 0;
            const advance = parseFloat(document.getElementById('advance').value) || 0;
            const remaining = amount - advance;
            document.getElementById('remaining').value = remaining >= 0 ? remaining : 0;
        }
    </script>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex items-center justify-center">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-50 to-orange-50 p-6 text-center border-b border-slate-200">
            <div class="flex justify-center mb-3">
                <img src="logo.jpeg" alt="FS Solar Logo" class="h-20 object-contain" onError="this.onerror=null; this.src='https://via.placeholder.com/200x80?text=FS+Solar';" />
            </div>
            <h2 class="text-xl font-bold text-slate-800">Edit Record #FS-<?php echo str_pad($entry['id'], 4, '0', STR_PAD_LEFT); ?></h2>
        </div>

        <!-- Error / Warning Message -->
        <?php if (!empty($message)): ?>
            <div class="m-6 mb-0 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-center font-medium">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Edit Form Section -->
        <form action="" method="POST" class="p-6 md:p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Name Field -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Customer Name</label>
                    <input type="text" id="name" name="name" required 
                        value="<?php echo htmlspecialchars($entry['customer_name']); ?>" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>

                <!-- Date Field -->
                <div>
                    <label for="date" class="block text-sm font-semibold text-slate-700 mb-1">Date</label>
                    <input type="date" id="date" name="date" required 
                        value="<?php echo htmlspecialchars($entry['entry_date']); ?>"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                </div>
            </div>

            <!-- Fault Field -->
            <div>
                <label for="fault" class="block text-sm font-semibold text-slate-700 mb-1">Fault / Issue Description</label>
                <textarea id="fault" name="fault" rows="3" required 
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"><?php echo htmlspecialchars($entry['fault']); ?></textarea>
            </div>

            <!-- Accessories Field -->
            <div>
                <label for="accessories" class="block text-sm font-semibold text-slate-700 mb-1">Accessories</label>
                <input type="text" id="accessories" name="accessories" 
                    value="<?php echo htmlspecialchars($entry['accessories']); ?>"
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
            </div>

            <!-- Note Field -->
            <div>
                <label for="note" class="block text-sm font-semibold text-slate-700 mb-1">Note</label>
                <textarea id="note" name="note" rows="2" 
                    class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"><?php echo htmlspecialchars($entry['note']); ?></textarea>
            </div>

            <!-- Payment Details (3 Grid Columns) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <!-- Total Amount -->
                <div>
                    <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1">Total Amount</label>
                    <input type="number" step="0.01" id="amount" name="amount" oninput="calculateRemaining()" 
                        value="<?php echo htmlspecialchars($entry['amount']); ?>" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                </div>

                <!-- Advance Amount -->
                <div>
                    <label for="advance" class="block text-sm font-semibold text-slate-700 mb-1">Advance Amount</label>
                    <input type="number" step="0.01" id="advance" name="advance" oninput="calculateRemaining()" 
                        value="<?php echo htmlspecialchars($entry['advance']); ?>" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                </div>

                <!-- Remaining Amount -->
                <div>
                    <label for="remaining" class="block text-sm font-semibold text-slate-700 mb-1">Remaining Amount</label>
                    <input type="number" step="0.01" id="remaining" name="remaining" readonly 
                        value="<?php echo htmlspecialchars($entry['remaining']); ?>" 
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-300 bg-slate-100 text-slate-600 font-bold outline-none cursor-not-allowed">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 pt-4">
                <a href="show.php?id=<?php echo $entry['id']; ?>" 
                    class="w-1/3 text-center bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-3 px-4 rounded-lg transition duration-200">
                    Cancel
                </a>
                <button type="submit" 
                    class="w-2/3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition duration-200">
                    Update Entry
                </button>
            </div>

        </form>
    </div>

</body>
</html>