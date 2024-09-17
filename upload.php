<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// 启动session
session_start();

// 如果 session 中的 'mysec' 变量未设置，则生成一个新的随机数字
if (!isset($_SESSION['mysec'])) {
    $_SESSION['mysec'] = rand(1, 100);
}

// 根据 session 值创建新的目录
$target_dir = "uploads/" . $_SESSION["mysec"] . "/";

// 检查目录是否存在，如果不存在则创建
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// 生成唯一的文件名
$target_file = $target_dir . $_SESSION["mysec"] . ".jpg";

$uploadOk = 1; // 上传文件的标志

// 检查文件是否是一张真正的图像文件
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        echo "上傳完成，書法字體轉換成功<br>";
        $uploadOk = 1;
    } else {
        echo "檔案不是一個有效的圖像。";
        $uploadOk = 0;
    }
}

// 检查文件大小
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "對不起，檔案太大。";
    $uploadOk = 0;
}

// 允许的文件格式
$allowed_extensions = array("jpg", "jpeg", "png", "gif");
$file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
if (!in_array($file_extension, $allowed_extensions)) {
    echo "對不起，只允許 JPG, JPEG, PNG 和 GIF 檔案格式。";
    $uploadOk = 0;
}

// 检查 $uploadOk 是否为 0
if ($uploadOk == 0) {
    echo "對不起，檔案未上傳。";
} else {
    // 如果一切正常，尝试上传文件
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        $input_dir = 'C:/xampp/htdocs/web/calligraphy_tku.com/uploads/' . $_SESSION['mysec'] . '/';
        $save_dir = 'C:/xampp/htdocs/web/calligraphy_tku.com/output/' . $_SESSION['mysec'] . '/';

        // 检查并创建输出目录
        if (!file_exists($save_dir)) {
            mkdir($save_dir, 0777, true);
        }

        // 生成命令
        $command = 'start /B cmd /k "cd /d C:\Users\lifelab\gan-v202305_gui && conda activate torch ' . 
                   '&& python infer2.py --input_dir=' . escapeshellarg($input_dir) . ' --save_dir=' . escapeshellarg($save_dir) . ' && conda deactivate"';

        // 执行命令
        exec($command);

        echo "Input Directory: " . $input_dir . "<br>";
        echo "Save Directory: " . $save_dir . "<br>";
        echo "target_dir: " . $target_dir . "<br>";
    } else {
        echo "對不起，上傳檔案時出現了錯誤。";
    }
}
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
<?php
// 要顯示的目錄路徑
$directory = "output/" . $_SESSION['mysec'] . "/";

// 列出目錄中的所有文件
$files = scandir($directory);

// 移除 . 和 .. 條目
$files = array_diff($files, array('.', '..'));
?>
<div class="gallery">
    <?php foreach ($files as $file) : ?>
        <?php $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION)); ?>
        <?php if (in_array($imageFileType, array("jpg", "jpeg", "png", "gif"))) : ?>
            <div class="gallery-item">
                <img src="<?php echo $directory . $file; ?>" alt="<?php echo $file; ?>">
                <p><?php echo $file; ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
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
