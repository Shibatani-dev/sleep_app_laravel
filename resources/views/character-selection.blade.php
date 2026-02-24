<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>キャラクター選択 - SleepPal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="character-selection-page">
    <div class="character-selection-container">
        <div class="character-selection-content">
            <!-- タイトル -->
            <h1 class="selection-title">選択してください</h1>
            
            <!-- フォーム -->
            <form method="POST" action="{{ route('character.select') }}" id="character-form">
                @csrf
                
                <!-- 隠しフィールド: 選択されたキャラクタータイプのIDを保存 -->
                <input type="hidden" name="character_type_id" id="selected-character-type-id">
                
                <!-- キャラクター一覧 -->
                <div class="characters-grid">
                    @forelse($characterTypes as $characterType)
                        <div class="character-card" data-character-type-id="{{ $characterType->id }}">
                            <!-- キャラクター画像ボックス -->
                            <div class="character-image-box">
                                <img 
                                    src="{{ asset($characterType->image_path) }}" 
                                    alt="{{ $characterType->name }}" 
                                    class="character-img"
                                >
                            </div>
                            
                            <!-- キャラクター説明テキスト -->
                            <p class="character-label">
                            {{ $characterType->name }}                            </p>
                        </div>
                    @empty
                        <div class="empty-state">
                            <p class="empty-title">キャラクターが見つかりません</p>
                            <p class="empty-sub">管理者に連絡してください</p>
                        </div>
                    @endforelse
                </div>
                
                <!-- エラーメッセージ表示 -->
                @error('character_type_id')
                    <p class="error-message">{{ $message }}</p>
                @enderror
                
                <!-- 決定ボタン -->
                <div class="submit-section">
                    <button 
                        type="submit" 
                        class="btn-character-submit" 
                        id="submit-btn" 
                        disabled
                    >
                        この子にする
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 全てのキャラクターカードを取得
            const cards = document.querySelectorAll('.character-card');
            
            // 決定ボタンを取得
            const submitBtn = document.getElementById('submit-btn');
            
            // 隠しフィールドを取得
            const characterTypeIdInput = document.getElementById('selected-character-type-id');
            
            // 各カードにクリックイベントを設定
            cards.forEach(card => {
                card.addEventListener('click', function() {
                    // 全てのカードから選択状態を解除
                    cards.forEach(c => c.classList.remove('selected'));
                    
                    // クリックされたカードを選択状態にする
                    this.classList.add('selected');
                    
                    // 選択されたキャラクタータイプのIDを取得
                    const characterTypeId = this.dataset.characterTypeId;
                    
                    // 隠しフィールドに値をセット
                    characterTypeIdInput.value = characterTypeId;
                    
                    // 決定ボタンを有効化
                    submitBtn.disabled = false;
                });
            });
        });
    </script>
</body>
</html>