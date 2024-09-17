import os
import io
import base64
import random
import subprocess
from flask import Flask, request, jsonify, send_file, render_template
from PIL import Image

app = Flask(__name__)

@app.route('/upload', methods=['POST'])
def upload_image():
    # 啟動會話
    session_id = request.cookies.get('session_id')
    if not session_id:
        session_id = str(random.randint(1, 100))
    
    # 接收從前端發送過來的圖片數據URL
    image_data = request.form.get('imageData')
    if not image_data:
        return jsonify({'error': 'imageData not set.'}), 400

    # 檢查圖片數據URL是否正確
    image_parts = image_data.split(',')
    if len(image_parts) != 2 or not image_parts[0].startswith('data:image'):
        return jsonify({'error': 'Invalid imageData format.'}), 400

    # 根據 session 值創建新的目錄
    target_dir = os.path.join('uploads', session_id)
    os.makedirs(target_dir, exist_ok=True)

    # 生成文件路徑
    target_file = os.path.join(target_dir, f'{session_id}.png')

    # 打開文件並寫入數據
    with open(target_file, 'wb') as file:
        file.write(base64.b64decode(image_parts[1]))

    # 做其他後續處理，例如返回成功或失敗信息給前端
    input_dir = os.path.join('uploads', session_id)
    save_dir = os.path.join('output', session_id)
    os.makedirs(save_dir, exist_ok=True)

    # 定義環境名稱變數
    conda_env = 'torch'

    # 定義目錄路徑變數
    gan_directory = 'C:\\Users\\lifelab\\gan-v202305_gui'

    # 構建命令以啟動 Python 腳本進行圖像處理
    command = f'cmd /c "cd /d {gan_directory} && conda activate {conda_env} && python infer2.py --input_dir={input_dir} --save_dir={save_dir} && conda deactivate"'
    result = subprocess.run(command, shell=True, capture_output=True, text=True)
    if result.returncode != 0:
        return jsonify({'error': f'Error executing command: {result.stderr}'}), 500

    # 列出處理後的圖像文件並顯示在前端
    files = os.listdir(save_dir)
    files = [f for f in files if f.lower().endswith(('png', 'jpeg', 'jpg', 'gif'))]
    return render_template('upload.html', files=files, directory=f'output/{session_id}/')

if __name__ == '__main__':
    app.run(host='192.168.0.12', port=5000, debug=True)