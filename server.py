from flask import Flask, request, jsonify, send_file
import os
import io
from PIL import Image, ImageDraw, ImageFont
import torchvision.transforms as transforms
import torch
from model import Zi2ZiModel
import numpy as np

app = Flask(__name__)

# 設定模型
model = Zi2ZiModel(
    input_nc=3,
    embedding_num=40,
    embedding_dim=128,
    Lconst_penalty=15,
    Lcategory_penalty=1.0,
    save_dir='./checkpoint',
    gpu_ids=['cuda:0'],
    is_training=False
)
model.setup()
model.print_networks(True)
model.load_networks(161850)

# 定義圖片轉換
transform = transforms.Compose([
    transforms.Resize((256, 256)),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.5], std=[0.5])
])

def draw_single_char(ch, font, canvas_size):
    img = Image.new("RGB", (canvas_size, canvas_size), (255, 255, 255))
    draw = ImageDraw.Draw(img)
    draw.text((0, 0), ch, (0, 0, 0), font=font)
    img = img.convert('L')
    return img



@app.route('/process_image', methods=['POST'])
def process_image():
    if 'image' not in request.files:
        return jsonify({"error": "No image uploaded"}), 400

    file = request.files['image']
    img = Image.open(io.BytesIO(file.read()))
    img = img.convert('RGB')

    # 保存上傳的圖片到指定目錄
    input_dir = 'path/to/input_dir'
    save_dir = 'path/to/save_dir'
    os.makedirs(input_dir, exist_ok=True)
    os.makedirs(save_dir, exist_ok=True)
    input_path = os.path.join(input_dir, 'input_image.jpg')
    img.save(input_path)

    # 執行命令
    gan_directory = 'C:\Users\lifelab\gan-v202305_gui'
    conda_env = 'torch'
    command = f'cmd /c "cd /d {gan_directory} && conda activate {conda_env} && python infer2.py --input_dir={input_dir} --save_dir={save_dir} && conda deactivate"'
    subprocess.run(command, shell=True)

    # 讀取生成的圖片
    result_path = os.path.join(save_dir, 'output_image.jpg')
    if not os.path.exists(result_path):
        return jsonify({"error": "Generated image not found"}), 500

    return send_file(result_path, mimetype='image/jpeg')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
