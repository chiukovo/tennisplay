<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>開發者登入 | LoveTennis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            margin: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .warning-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            outline: none;
            border-color: #00d9ff;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 20px rgba(0, 217, 255, 0.2);
        }
        
        input[type="text"]::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #00d9ff, #00ff88);
            color: #1a1a2e;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.4);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .hint {
            margin-top: 20px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.5;
        }
        
        .hint code {
            background: rgba(0, 217, 255, 0.2);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            color: #00d9ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="warning-badge">⚠️ 僅限本機開發使用</div>
        <h1>開發者登入</h1>
        <p class="subtitle">輸入 LINE ID 快速登入或建立測試帳號</p>
        
        <form action="{{ route('dev.login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="line_id">LINE User ID</label>
                <input 
                    type="text" 
                    id="line_id" 
                    name="line_id" 
                    placeholder="例如：U1234567890abcdef..."
                    required
                    autofocus
                >
            </div>
            
            <button type="submit">登入 / 建立帳號</button>
        </form>
        
        <div class="hint">
            <strong>提示：</strong><br>
            • 若 LINE ID 不存在，系統會自動建立新帳號<br>
            • 此頁面僅在 <code>APP_ENV=local</code> 時可用<br>
            • 可使用任意字串作為測試 LINE ID
        </div>
    </div>
</body>
</html>
