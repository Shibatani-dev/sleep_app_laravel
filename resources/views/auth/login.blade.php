<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ログイン - SleepPal</title>
    
    <!-- Google Fonts - 丸ゴシック -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <div class="login-container">
        <!-- 雲（左上） -->
        <div class="cloud cloud-1"></div>
        
        <!-- 雲（右上） -->
        <div class="cloud cloud-2"></div>
        
        <!-- 雲（左下） -->
        <div class="cloud cloud-3"></div>
        
        <!-- メインコンテンツ -->
        <div class="login-content">
            <div class="login-box">
                <h1 class="login-title">ログイン</h1>
                
                <!-- Session Status -->
                @if (session('status'))
                    <div class="status-message">
                        {{ session('status') }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <!-- E-mailアドレス -->
                    <div class="form-group">
                        <label for="email" class="form-label">E-mailアドレス</label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
                            autofocus 
                            autocomplete="username"
                            class="form-input"
                        />
                        @error('email')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- パスワード -->
                    <div class="form-group">
                        <label for="password" class="form-label">パスワード</label>
                        <input 
                            id="password" 
                            type="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            class="form-input"
                        />
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- ログインボタン -->
                    <div class="form-submit">
                        <button type="submit" class="btn-submit">ログイン</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>