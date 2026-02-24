<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ショップ - SleePal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="shop-page">
    <!-- ヘッダー -->
    <header class="site-header">
        <div class="header-left">
            <a href="{{ route('home') }}" class="home-icon-link">
                <img src="{{ asset('images/characters/home.png') }}" alt="ホーム" class="home-icon">
            </a>
        </div>
        <div class="header-center">
            <h1 class="site-logo">SleePal</h1>
        </div>
        <div class="header-right">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </div>
    </header>

    <div class="shop-container">
        <!-- 左側の雲の装飾 -->
        <aside class="cloud-decoration-left">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-3"></div>
        </aside>

        <!-- メインコンテンツ -->
        <main class="shop-main">
            <!-- ページタイトル -->
            <div class="shop-title-wrapper">
                <h2 class="shop-title">ショップ</h2>
            </div>

            @if(isset($items) && $items->count() > 0)
                <!-- キャラクタープレビューエリア -->
                <div class="character-preview-area">
                    <div class="character-preview-box">
                        <!-- キャラクター表示 -->
                        <div class="preview-character">
                        @php
                            $level = max(1, min(3, auth()->user()->character->level ?? 1));
                            $basePath = auth()->user()->characterType
                                ? auth()->user()->characterType->image_path
                                : 'images/characters/default.png';

                            if ($level === 1) {
                                $imagePath = $basePath;
                            } elseif ($level === 2) {
                                $imagePath = preg_replace('/(\.\w+)$/', '_lv2$1', $basePath);
                            } else {
                                $imagePath = preg_replace('/(\.\w+)$/', '_lv3$1', $basePath);
                            }
                        @endphp                            
                            <img 
                                src="{{ asset($imagePath) }}" 
                                alt="{{ auth()->user()->characterType ? auth()->user()->characterType->name : 'キャラクター' }}"
                                class="character-base-image"
                                id="characterImage"
                            >
                            <!-- 試着中のアイテムを重ねて表示 -->
                            <img 
                                src="" 
                                alt="試着アイテム" 
                                class="character-accessory-image" 
                                id="accessoryPreview"
                                style="display: none;"
                            >
                        </div>
                <div class="character-info">
                    <h2 class="character-name">
                        Lv.{{ $character->level }} {{ $character->name }}
                    </h2>
                </div>
                    </div>
                </div>

                <!-- アイテムリスト -->
                <div class="items-list-area">
                    @foreach($items as $item)
                        <div class="item-display-card" 
                             data-item-id="{{ $item->id }}" 
                             data-item-image="{{ $item->image_path ? asset($item->image_path) : '' }}"
                             data-is-owned="{{ $userItems->contains($item->id) ? 'true' : 'false' }}"
                             onclick="previewItem(this)">
                            <div class="item-display-image">
                                @if($item->image_path)
                                    <img src="{{ asset($item->image_path) }}" alt="{{ $item->name }}" class="item-img">
                                @else
                                    <div class="item-placeholder-text">
                                        ショップで買える<br>アクセサリーの<br>画像表示
                                    </div>
                                @endif
                            </div>
                            
                            <!-- 購入済みバッジ -->
                            @if($userItems->contains($item->id))
                                <div class="owned-badge">購入済み</div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- アクションボタン -->
                <div class="action-buttons-area">
                    <button type="button" class="btn-action btn-remove" id="removeBtn">取り外す</button>
                    <button type="button" class="btn-action btn-equip" id="equipBtn" disabled>装着する</button>
                    <button type="button" class="btn-action btn-purchase" id="purchaseBtn" disabled>購入する</button>
                </div>
            @else
                <div class="no-items">
                    <div class="no-items-icon">🛍️</div>
                    <p class="no-items-text">現在購入可能なアイテムはありません</p>
                </div>
            @endif
        </main>

        <!-- 右側の雲の装飾 -->
        <aside class="cloud-decoration-right">
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-4"></div>
        </aside>
    </div>

    <script>
        let selectedItemId = null;
        let selectedItemImage = null;
        let isItemOwned = false;

        // アイテムをプレビュー（クリックで試着）
        function previewItem(card) {
            // 他のカードの選択を解除
            document.querySelectorAll('.item-display-card').forEach(c => {
                c.classList.remove('selected');
            });
            
            // このカードを選択
            card.classList.add('selected');
            selectedItemId = card.dataset.itemId;
            selectedItemImage = card.dataset.itemImage;
            isItemOwned = card.dataset.isOwned === 'true';
            
            // プレビュー表示（お試し装着）
            const accessoryPreview = document.getElementById('accessoryPreview');
            if (selectedItemImage) {
                accessoryPreview.src = selectedItemImage;
                accessoryPreview.style.display = 'block';
            }
            
            // ボタンの状態を更新
            updateButtons();
            
            console.log('Selected item:', selectedItemId, 'Owned:', isItemOwned);
        }

        // ボタンの状態を更新
        function updateButtons() {
            const equipBtn = document.getElementById('equipBtn');
            const purchaseBtn = document.getElementById('purchaseBtn');
            
            if (selectedItemId) {
                if (isItemOwned) {
                    // 購入済みの場合：装着ボタンのみ有効
                    equipBtn.disabled = false;
                    purchaseBtn.disabled = true;
                } else {
                    // 未購入の場合：購入ボタンのみ有効
                    equipBtn.disabled = true;
                    purchaseBtn.disabled = false;
                }
            } else {
                // 何も選択していない場合：両方無効
                equipBtn.disabled = true;
                purchaseBtn.disabled = true;
            }
        }

        // 取り外すボタン
        document.getElementById('removeBtn')?.addEventListener('click', function() {
            const accessoryPreview = document.getElementById('accessoryPreview');
            accessoryPreview.style.display = 'none';
            accessoryPreview.src = '';
            
            // 選択を解除
            document.querySelectorAll('.item-display-card').forEach(c => {
                c.classList.remove('selected');
            });
            selectedItemId = null;
            selectedItemImage = null;
            isItemOwned = false;
            
            updateButtons();
            
            console.log('Accessory removed');
        });

        // 装着するボタン（購入済みアイテムのみ）
        document.getElementById('equipBtn')?.addEventListener('click', function() {
            if (!selectedItemId || !isItemOwned) {
                alert('購入済みのアイテムのみ装着できます');
                return;
            }

            // 装着処理
            fetch(`/shop/${selectedItemId}/toggle-equip`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('装着しました！');
                    // プレビューはそのまま表示
                } else {
                    alert(data.message || '装着に失敗しました');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('エラーが発生しました');
            });
        });

        // 購入するボタン
        document.getElementById('purchaseBtn')?.addEventListener('click', function() {
            if (!selectedItemId || isItemOwned) {
                alert('未購入のアイテムを選択してください');
                return;
            }

            if (confirm('このアイテムを購入しますか？')) {
                // 購入処理
                fetch(`/shop/${selectedItemId}/purchase`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('購入しました！');
                        location.reload();
                    } else {
                        alert(data.message || '購入に失敗しました');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('エラーが発生しました');
                });
            }
        });
    </script>
</body>
</html>