<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 啟動會話
session_start();
if (!isset($_SESSION['mysec'])) {
    $_SESSION['mysec'] = rand(1, 100);
}
file_put_contents('debug.txt', print_r($_POST, true));
// 接收從前端發送過來的圖片數據URL
if (!isset($_POST['imageData'])) {
    //輸出imageData
    echo json_encode(['imageData' => $_POST['imageData']]);
    die("Error: imageData not set.");
}

$imageData = $_POST['imageData'];

// 檢查圖片數據URL是否正確
$imageParts = explode(',', $imageData);
if (count($imageParts) !== 2 || strpos($imageParts[0], 'data:image') === false) {
    //die("Error: Invalid imageData format.");
}

// 根據 session 值創建新的目錄
$target_dir = "uploads/" . $_SESSION["mysec"] . "/";

// 檢查目錄是否存在，如果不存在則創建
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// 生成文件路徑
$target_file = $target_dir . $_SESSION["mysec"]. ".png";

// 打開文件並寫入數據
$file = fopen($target_file, 'wb');
fwrite($file, base64_decode($imageParts[1]));
fclose($file);

// 做其他後續處理，例如返回成功或失敗信息給前端
//echo json_encode(['file' => $target_file]);

$input_dir = 'C:/xampp/htdocs/web/calligraphy_tku.com/uploads/' . $_SESSION['mysec'] . '/';
$save_dir = 'C:/xampp/htdocs/web/calligraphy_tku.com/output/' . $_SESSION['mysec'] . '/';

// 確保輸出目錄存在
if (!file_exists($save_dir)) {
    mkdir($save_dir, 0777, true);
}

// 定義環境名稱變數
$conda_env = 'torch';

// 定義目錄路徑變數
$gan_directory = 'C:\Users\lifelab\gan-v202305_gui';

// 構建命令以啟動 Python 腳本進行圖像處理
$command = 'start /B cmd /c "cd /d ' . escapeshellarg($gan_directory) . ' && conda activate ' . escapeshellarg($conda_env) . ' ' . 
           '&& python infer2.py --input_dir=' . escapeshellarg($input_dir) . ' --save_dir=' . escapeshellarg($save_dir) . ' && conda deactivate"';
// 執行命令
$output = [];
$return_var = 0;
exec($command, $output, $return_var);
if ($return_var !== 0) {
    echo "Error executing command: " . implode("\n", $output);
}

// 列出處理後的圖像文件並顯示在前端
$directory = "output/" . $_SESSION['mysec'] . "/";

// 列出目錄中的所有文件
$files = scandir($directory);

// 移除 . 和 .. 條目
$files = array_diff($files, array('.', '..'));
?>


<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>圖片上傳</title>
    <style>
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            grid-gap: 20px;
        }
        .gallery img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
<h2>圖片庫</h2>

<div class="gallery">
    <?php foreach ($files as $file) : ?>
        <?php $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION)); ?>
        <?php if (in_array($imageFileType, array("png", "jpeg", "jpg", "gif"))) : ?>
            <div class="gallery-item">
                <img src="<?php echo $directory . $file; ?>" alt="<?php echo $file; ?>">
                <button onclick="downloadImage('<?php echo $directory . $file; ?>')">下載</button>
                <br> <!-- 添加空行 -->
            </div>
        <?php endif; ?>
    <?php endforeach; ?> 
</div>
<br> <!-- 添加空行 -->
<button id="prevBtn">上一張</button>
<button id="nextBtn">下一張</button>

<script>
    var currentIndex = 0;
    var galleryItems = document.querySelectorAll('.gallery-item');

    function showImage(index) {
        if (index < 0) {
            index = galleryItems.length - 1;
        } else if (index >= galleryItems.length) {
            index = 0;
        }

        galleryItems.forEach(function(item, i) {
            item.style.display = (i === index) ? 'block' : 'none';
        });

        currentIndex = index;
    }

    function downloadImage(url) {
        const a = document.createElement('a');
        a.href = url;
        a.download = url.split('/').pop();
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    document.getElementById('prevBtn').addEventListener('click', function() {
        showImage(currentIndex - 1);
    });

    document.getElementById('nextBtn').addEventListener('click', function() {
        showImage(currentIndex + 1);
    });

    // 初始化顯示第一張圖片
    showImage(0);
</script>
</body>
</html>
