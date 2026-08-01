<?php
require_once 'connection.php';

// 1. Database SQL Backup File Download Logic
if (isset($_GET['action']) && $_GET['action'] == 'download_backup') {
    // Project directory me `.sql` files talash karein
    $sql_files = glob("*.sql");

    if (!empty($sql_files)) {
        // Sab se late create/modify hone wali file pick karein
        usort($sql_files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $file_to_download = $sql_files[0];

        if (file_exists($file_to_download)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_to_download) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_to_download));
            readfile($file_to_download);
            exit;
        }
    }
    
    die("<script>alert('Koi SQL Backup file project folder me nahi mili!'); window.history.back();</script>");
}

// 2. Delete Logic
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);
    $stmt = $pdo->prepare("DELETE FROM service_entries WHERE id = :id");
    $stmt->execute([':id' => $delete_id]);
    header("Location: index.php");
    exit;
}

// 3. ID se Data fetch kar rahe hain
$id = $_GET['id'] ?? null;
$entry = null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM service_entries WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $entry = $stmt->fetch();
}

if (!$entry) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Record nahi mila!</h2><a href='index.php'>Wapas Form Par Jayein</a></div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Receipt #<?php echo $entry['id']; ?> - FS Solar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Print Styles - Jab Print button dabayein to sirf slip print ho */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff;
                padding: 0;
            }
            .print-card {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex flex-col items-center justify-center">

    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200 print-card">
        
        <!-- Top Action Buttons (New Entry, Edit, Delete, Backup, Print) -->
        <div class="no-print bg-slate-800 p-4 flex flex-wrap gap-2 justify-between items-center">
            <a href="index.php" class="text-slate-300 hover:text-white text-sm font-semibold">← New Entry</a>
            
            <div class="flex flex-wrap gap-2 items-center">
                <!-- Backup Download Button -->
                <a href="show.php?id=<?php echo $entry['id']; ?>&action=download_backup" 
                   class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-3 py-2 rounded-lg text-sm transition shadow flex items-center gap-1"
                   title="Download SQL Database Backup">
                   💾 <span>Backup SQL</span>
                </a>

                <!-- Edit Button -->
                <a href="edit.php?id=<?php echo $entry['id']; ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-3 py-2 rounded-lg text-sm transition shadow">
                   ✎ Edit
                </a>

                <!-- Delete Button -->
                <a href="show.php?id=<?php echo $entry['id']; ?>&action=delete" 
                   onclick="return confirm('Kya aap is record ko delete karna chahte hain?');" 
                   class="bg-red-600 hover:bg-red-700 text-white font-semibold px-3 py-2 rounded-lg text-sm transition shadow">
                   🗑 Delete
                </a>

                <!-- Print Button -->
                <button onclick="window.print()" 
                        class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-3 py-2 rounded-lg text-sm transition shadow flex items-center gap-1">
                   🖨 Print
                </button>
            </div>
        </div>

        <!-- Header Section with Logo -->
        <div class="bg-gradient-to-r from-blue-50 to-orange-50 p-6 text-center border-b border-slate-200">
            <div class="flex justify-center mb-2">
                <img src="logo.jpeg" alt="FS Solar Logo" class="h-20 object-contain" onError="this.onerror=null; this.src='https://via.placeholder.com/200x80?text=FS+Solar';" />
            </div>
            <p class="text-xs tracking-wider text-slate-500 uppercase font-semibold">Energy & Security Solution</p>
        </div>

        <!-- Receipt Content -->
        <div class="p-6 md:p-8 space-y-6">
            
            <div class="flex justify-between items-center border-b pb-4">
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase">Receipt No</span>
                    <h3 class="text-xl font-extrabold text-blue-600">#FS-<?php echo str_pad($entry['id'], 4, '0', STR_PAD_LEFT); ?></h3>
                </div>
                <div class="text-right">
                    <span class="text-xs text-slate-400 font-bold uppercase">Date</span>
                    <p class="text-slate-700 font-semibold"><?php echo date('d M, Y', strtotime($entry['entry_date'])); ?></p>
                </div>
            </div>

            <!-- Customer Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase">Customer Name</span>
                    <p class="text-lg font-bold text-slate-800"><?php echo htmlspecialchars($entry['customer_name']); ?></p>
                </div>
                <div>
                    <span class="text-xs text-slate-400 font-bold uppercase">Accessories</span>
                    <p class="text-slate-700 font-medium"><?php echo htmlspecialchars($entry['accessories']) ?: 'N/A'; ?></p>
                </div>
            </div>

            <!-- Fault Details -->
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase">Fault / Problem Reported</span>
                <p class="mt-1 p-3 bg-red-50 text-red-700 rounded-lg border border-red-100 font-medium">
                    <?php echo nl2br(htmlspecialchars($entry['fault'])); ?>
                </p>
            </div>

            <!-- Note Details -->
            <?php if(!empty($entry['note'])): ?>
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase">Additional Note</span>
                <p class="mt-1 p-3 bg-amber-50 text-amber-800 rounded-lg border border-amber-100 text-sm">
                    <?php echo nl2br(htmlspecialchars($entry['note'])); ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Financial Summary -->
            <div class="border-t pt-4">
                <h4 class="text-xs font-bold text-slate-400 uppercase mb-3">Payment Summary</h4>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 bg-slate-50 rounded-lg border">
                        <span class="block text-xs text-slate-500 font-semibold">Total Amount</span>
                        <span class="text-lg font-bold text-slate-800">Rs. <?php echo number_format($entry['amount'], 2); ?></span>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <span class="block text-xs text-blue-600 font-semibold">Advance</span>
                        <span class="text-lg font-bold text-blue-700">Rs. <?php echo number_format($entry['advance'], 2); ?></span>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-lg border border-orange-100">
                        <span class="block text-xs text-orange-600 font-semibold">Remaining</span>
                        <span class="text-lg font-bold text-orange-700">Rs. <?php echo number_format($entry['remaining'], 2); ?></span>
                    </div>
                </div>
            </div>

            <!-- Signature Area -->
            <div class="pt-8 flex justify-between items-end">
                <div class="text-xs text-slate-400">
                    <p>Thank you for choosing FS Solar!</p>
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
            </div>

        </div>
    </div>

</body>
</html>