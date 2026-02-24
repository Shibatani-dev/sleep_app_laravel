<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>過去の睡眠記録 - SleePal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sleep-history-page">
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

    <div class="history-container">
        <!-- 左側の雲の装飾 -->
        <aside class="cloud-decoration-left">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-3"></div>
        </aside>

        <!-- メインコンテンツ -->
        <main class="history-main">
            <!-- ページタイトル -->
            <div class="history-title-wrapper">
                <h2 class="history-title">過去の睡眠記録</h2>
            </div>

            <!-- 記録リスト -->
            <!-- 記録が一件以上ある場合のみ表示 -->
            <!-- isset()でnullチェック、count()で０件チェックを両方している -->
            @if(isset($records) && $records->count() > 0)
                <div class="records-list">
                    <!-- 記録を１件ずつループして表示 -->
                    @foreach($records as $record)
                        <div class="record-box">
                            <div class="record-inner">
                                <!-- モーダル編集 -->
                                <div class="record-menu">
                                    <!-- onclickでそのレコードのIDを渡してメニューを開く -->
                                    <button type="button" class="menu-btn" onclick="toggleMenu({{ $record->id }})">⋮</button>
                                    <div class="menu-dropdown" id="menu-{{ $record->id }}">
                                        <button type="button" class="menu-item" onclick="openEditModal({{ $record->id }})">
                                            ✏️ 編集
                                        </button>
                                        <button type="button" class="menu-item menu-item-delete" onclick="confirmDelete({{ $record->id }})">
                                            🗑️ 削除
                                        </button>
                                    </div>
                                </div>

                                <!-- 日付 Carbonで取得-->
                                <div class="record-date">
                                    {{ \Carbon\Carbon::parse($record->date)->format('Y年m月d日') }}
                                    ({{ ['日', '月', '火', '水', '木', '金', '土'][\Carbon\Carbon::parse($record->date)->dayOfWeek] }})
                                </div>

                                <!-- 睡眠データ -->
                                <div class="record-stats">
                                    <div class="record-stat-item">
                                        <div class="record-stat-label">就寝時刻</div>
                                        <div class="record-stat-value">{{ \Carbon\Carbon::parse($record->bedtime)->format('H:i') }}</div>
                                    </div>
                                    <div class="record-stat-item">
                                        <div class="record-stat-label">起床時刻</div>
                                        <div class="record-stat-value">{{ \Carbon\Carbon::parse($record->wakeup)->format('H:i') }}</div>
                                    </div>
                                    <div class="record-stat-item">
                                        <div class="record-stat-label">睡眠時間</div>
                                        <div class="record-stat-value">{{ number_format($record->hours, 1) }}時間</div>
                                    </div>
                                </div>

                                <!-- 獲得ポイントが１以上の場合のみ表示 -->
                                @if($record->points_earned > 0)
                                    <div class="record-points">
                                        <span class="record-points-label">獲得ポイント:</span>
                                        <span class="record-points-value">{{ $record->points_earned }}pt</span>
                                    </div>
                                @endif

                                <!-- メモ -->
                                @if($record->memo)
                                    <div class="record-memo">
                                        <div class="record-memo-label">メモ</div>
                                        <div class="record-memo-text">{{ $record->memo }}</div>
                                    </div>
                                @endif

                                <!-- 入力時刻 -->
                                <div class="record-input-time">
                                    <small class="text-muted">
                                        記録日時: {{ \Carbon\Carbon::parse($record->input_time)->format('Y/m/d H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- ページネーション -->
                <!-- hasPages()葉総件数がperPage()より多い場合にtrueを返す -->
                @if($records->hasPages())
                    <div class="pagination-wrapper">
                        @if($records->onFirstPage())
                            <button class="pagination-arrow pagination-prev" disabled>←</button>
                        @else
                            <a href="{{ $records->previousPageUrl() }}" class="pagination-arrow pagination-prev">←</a>
                        @endif
                        
                        <span class="pagination-info">
                            {{ $records->currentPage() }} / {{ $records->lastPage() }} ページ
                        </span>

                        <!-- 記録が０件の場合の表示 -->
                        @if($records->hasMorePages())
                            <a href="{{ $records->nextPageUrl() }}" class="pagination-arrow pagination-next">→</a>
                        @else
                            <button class="pagination-arrow pagination-next" disabled>→</button>
                        @endif
                    </div>
                @endif
            @else
                <div class="no-records">
                    <div class="no-records-icon">😴</div>
                    <p class="no-records-text">まだ睡眠記録がありません</p>
                    <a href="{{ route('sleep.create') }}" class="btn-create-first">
                        最初の記録を追加
                    </a>
                </div>
            @endif
        </main>

        <!-- 右側の雲の装飾 -->
        <aside class="cloud-decoration-right">
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-4"></div>
        </aside>
    </div>

<!-- 編集モーダル -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">睡眠記録を編集</h3>
            <button type="button" class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            
            <!-- 日付表示（編集不可） -->
            <div class="form-group">
                <label class="form-label">日付</label>
                <div class="form-display-field" id="edit-date-display"></div>
                <input type="hidden" name="date" id="edit-date">
            </div>

            <div class="form-group">
                <label class="form-label">就寝時刻</label>
                <input type="time" name="bedtime" id="edit-bedtime" class="form-input" step="300" required>
            </div>

            <div class="form-group">
                <label class="form-label">起床時刻</label>
                <input type="time" name="wakeup" id="edit-wakeup" class="form-input" step="300" required>
            </div>

            <div class="form-group">
                <label class="form-label">メモ（任意）</label>
                <textarea name="memo" id="edit-memo" class="form-textarea" rows="3"></textarea>
            </div>

            <div class="edit-warning">
                ⚠️ 編集すると獲得ポイントが再計算されます
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">キャンセル</button>
                <button type="submit" class="btn-save">保存</button>
            </div>
        </form>
    </div>
</div>
<!-- 削除確認モーダル -->
    <div id="deleteModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3 class="modal-title">削除の確認</h3>
            </div>
            <div class="modal-body">
                <p class="delete-warning">この睡眠記録を削除してもよろしいですか？</p>
                <p class="delete-info">獲得したポイントも取り消されます。</p>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeDeleteModal()">キャンセル</button>
                    <button type="submit" class="btn-delete">削除する</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        console.log('Script loaded'); // デバッグ用

        // メニュー表示切り替え
        function toggleMenu(recordId) {
            console.log('Toggle menu:', recordId); // デバッグ用
            const menu = document.getElementById(`menu-${recordId}`);
            const allMenus = document.querySelectorAll('.menu-dropdown');
            
            // 他のメニューを閉じる
            allMenus.forEach(m => {
                if (m !== menu) m.classList.remove('active');
            });
            
            // クリックしたメニューを切り替え
            menu.classList.toggle('active');
        }

        // メニュー外クリックで閉じる
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.record-menu')) {
                document.querySelectorAll('.menu-dropdown').forEach(m => {
                    m.classList.remove('active');
                });
            }
        });

// 編集モーダルを開く
function openEditModal(recordId) {
    console.log('Opening edit modal for record:', recordId);
    
    const url = `/sleep/${recordId}/edit`;
    console.log('Fetching from:', url);
    
    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Data received:', data);
        
        // 日付を表示用にフォーマット（読み取り専用表示）
        const dateObj = new Date(data.date);
        const year = dateObj.getFullYear();
        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
        const day = String(dateObj.getDate()).padStart(2, '0');
        const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][dateObj.getDay()];
        
        document.getElementById('edit-date-display').textContent = 
            `${year}年${month}月${day}日 (${dayOfWeek})`;
        document.getElementById('edit-date').value = data.date;
        
        // 時刻を HH:MM 形式に変換（秒を削除）
        const formatTime = (timeString) => {
            if (!timeString) return '';
            // HH:MM:SS を HH:MM に変換
            return timeString.substring(0, 5);
        };
        
        document.getElementById('edit-bedtime').value = formatTime(data.bedtime);
        document.getElementById('edit-wakeup').value = formatTime(data.wakeup);
        document.getElementById('edit-memo').value = data.memo || '';
        document.getElementById('editForm').action = `/sleep/${recordId}`;
        document.getElementById('editModal').classList.add('active');
        
        // メニューを閉じる
        document.querySelectorAll('.menu-dropdown').forEach(m => {
            m.classList.remove('active');
        });
    })
    .catch(error => {
        console.error('Error:', error);
        alert('データの取得に失敗しました: ' + error.message);
    });

}

function confirmDelete(recordId) {
    document.getElementById('deleteForm').action = `/sleep/${recordId}`;
    document.getElementById('deleteModal').style.display = 'flex';
    document.querySelectorAll('.menu-dropdown').forEach(m => m.classList.remove('active'));
}

    function closeDeleteModal() {
        document.getElementById('deleteModal').style.display = 'none';
    }

</script>
</body>
</html>