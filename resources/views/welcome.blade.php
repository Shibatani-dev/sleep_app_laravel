<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SleepPal - たのしく おやすみ</title>
    
    <!-- Google Fonts - 丸ゴシック -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="welcome-page">
    <div class="welcome-container">
        <!-- 雲（左上） -->
        <div class="cloud cloud-1"></div>
        
        <!-- 雲（右上） -->
        <div class="cloud cloud-2"></div>
        
        <!-- 雲（左下） -->
        <div class="cloud cloud-3"></div>
        
        <!-- 雲（右下） -->
        <div class="cloud cloud-4"></div>
        
        <!-- 雲（右中） -->
        <div class="cloud cloud-5"></div>
        
        <!-- メインコンテンツ -->
        <div class="welcome-content">
            <!-- ロゴとキャラクター -->
            <div class="welcome-header">
                <div class="welcome-logo">
                    <span class="logo-subtitle">たのしく　おやすみ</span>
                    <h1 class="logo-title">SleepPal</h1>
                </div>
                
                <!-- キャラクター（画像版） -->
                <div class="character-container">
                    <img src="{{ asset('images/characters/main-character.png') }}" alt="SleepPal キャラクター" class="character-image">
                </div>
            </div>
            
            <!-- ボタン -->
            <div class="welcome-buttons">
                <a href="{{ route('register') }}" class="btn btn-register">新規登録</a>
                <a href="{{ route('login') }}" class="btn btn-login">ログイン</a>
            </div>
        </div>
    </div>
</body>
</html>