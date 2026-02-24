<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>新規登録 - SleepPal</title>
    
    <!-- Google Fonts - 丸ゴシック -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="register-page">
    <div class="register-container">
        <!-- 雲（左上） -->
        <div class="cloud cloud-1"></div>
        
        <!-- 雲（右上） -->
        <div class="cloud cloud-2"></div>
        
        <!-- 雲（左下） -->
        <div class="cloud cloud-3"></div>
        
        <!-- メインコンテンツ -->
        <div class="register-content">
            <div class="register-box">
                <h1 class="register-title">新規登録</h1>
                
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    
                    <!-- お名前 -->
                    <div class="form-group">
                        <label for="name" class="form-label">お名前</label>
                        <input 
                            id="name" 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}" 
                            required 
                            autofocus 
                            autocomplete="name"
                            class="form-input"
                        />
                        @error('name')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- E-mailアドレス -->
                    <div class="form-group">
                        <label for="email" class="form-label">E-mailアドレス</label>
                        <input 
                            id="email" 
                            type="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required 
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
                            autocomplete="new-password"
                            class="form-input"
                        />
                        @error('password')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- パスワード再入力 -->
                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">パスワード再入力</label>
                        <input 
                            id="password_confirmation" 
                            type="password" 
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password"
                            class="form-input"
                        />
                        @error('password_confirmation')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- 登録ボタン -->
                    <div class="form-submit">
                        <button type="submit" class="btn-submit">登録</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>