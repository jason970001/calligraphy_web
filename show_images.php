<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>顯示圖片</title>
</head>
<body>

<h2>資料夾中的圖片</h2>

<?php
// 要顯示的目錄路徑
$directory = "output/";

// 列出目錄中的所有文件
$files = scandir($directory);

// 移除 . 和 .. 條目
$files = array_diff($files, array('.', '..'));


// 顯示每個圖片
foreach ($files as $file) {
    // 檢查檔案類型是圖片
    $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
        echo '<img src="' . $directory . $file . '" alt="' . $file . '"><br>';
        
    }
}
?>

</body>
</html>
