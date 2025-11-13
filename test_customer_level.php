<?php
// Test file để kiểm tra xem file view.php có được load đúng không
$file_content = file_get_contents('/Users/dangkhoa/Documents/webcaphe/lab1/admin/orders/view.php');

// Kiểm tra có "Cấp độ khách hàng" không
if (strpos($file_content, 'Cấp độ khách hàng') !== false) {
    echo "✓ YES - 'Cấp độ khách hàng' FOUND in view.php file!<br>";
    echo "Total occurrences: " . substr_count($file_content, 'Cấp độ khách hàng') . "<br><br>";
} else {
    echo "✗ NO - 'Cấp độ khách hàng' NOT found in view.php<br>";
}

// Kiểm tra có customer_level form không
if (strpos($file_content, 'name="customer_level"') !== false) {
    echo "✓ YES - customer_level form field FOUND!<br>";
} else {
    echo "✗ NO - customer_level form field NOT found<br>";
}

// Kiểm tra có customer_levels array không
if (strpos($file_content, '$customer_levels') !== false) {
    echo "✓ YES - \$customer_levels array FOUND!<br>";
} else {
    echo "✗ NO - \$customer_levels array NOT found<br>";
}

// Kiểm tra có update_customer_level handler không
if (strpos($file_content, "update_customer_level") !== false) {
    echo "✓ YES - update_customer_level handler FOUND!<br>";
} else {
    echo "✗ NO - update_customer_level handler NOT found<br>";
}
?>
