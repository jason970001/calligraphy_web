<?php
if (!isset($_POST['imageData'])) {
    die("Error: imageData not set.");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['imageData'])) {
    $imageData = $_POST['imageData'];

    // 移除 Base64 編碼的開頭部分
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = str_replace(' ', '+', $imageData);

    // 將 Base64 編碼的數據解碼為二進制數據
    $image = base64_decode($imageData);

    // 設置保存圖片的文件名和路徑
    $filePath = 'uploads/image.png';

    // 將解碼後的二進制數據保存為文件
    if (file_put_contents($filePath, $image)) {
        echo "圖片已成功保存！";
        window.location.href = 'save_image.php';
    } else {
        echo "圖片保存失敗。";
    }
}


?>
