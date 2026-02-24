<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>睡眠を記録する - SleePal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/drum-picker.css') }}">
</head>
<body class="sleep-create-page">
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


    <div class="main-layout">
        <!-- 左サイドバー（雲の装飾） -->
        <aside class="decoration-sidebar decoration-sidebar-left">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
        </aside>
        <!-- メインコンテンツ -->
        <main class="main-content">
            <div class="record-card">
                <h2 class="record-title">すいみん記録</h2>

                <form action="{{ route('sleep.store') }}" method="POST">
                    @csrf

                    <!-- 記録日 -->
                    <div class="form-group">
                        <label class="form-label">記録日</label>
                        <input 
                            type="date" 
                            name="date" 
                            value="{{ old('date', date('Y-m-d')) }}"
                            max="{{ date('Y-m-d') }}"
                            class="form-input"
                            required
                        >
                        @error('date')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 就寝時刻 -->
                    <div class="form-group">
                        <label class="form-label">就寝時刻</label>
                        <div class="drum-picker-wrapper">
                            <div class="drum-picker" id="bedtime-picker">
                                <div class="drum-column" id="bedtime-picker-h"></div>
                                <div class="drum-colon">:</div>
                                <div class="drum-column" id="bedtime-picker-m"></div>
                            </div>
                        </div>
                        <input type="hidden" name="bedtime" id="bedtime-picker-val">
                        @error('bedtime')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 起床時刻 -->
                    <div class="form-group">
                        <label class="form-label">起床時刻</label>
                        <div class="drum-picker-wrapper">
                            <div class="drum-picker" id="wakeup-picker">
                                <div class="drum-column" id="wakeup-picker-h"></div>
                                <div class="drum-colon">:</div>
                                <div class="drum-column" id="wakeup-picker-m"></div>
                            </div>
                        </div>
                        <input type="hidden" name="wakeup" id="wakeup-picker-val">
                        @error('wakeup')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- メモ（任意） -->
                    <div class="form-group">
                        <label class="form-label">メモ（任意）</label>
                        <textarea 
                            name="memo"
                            class="form-textarea"
                            placeholder="よく眠れた理由、夢の内容など..."
                        >{{ old('memo') }}</textarea>
                        @error('memo')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 送信ボタン -->
                    <div class="button-group">
                        <button type="submit" class="btn-submit">
                            記録する
                        </button>
                    </div>
                </form>
            </div>
        </main>

        <!-- 右サイドバー（雲の装飾） -->
        <aside class="decoration-sidebar">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
        </aside>
    </div>
    <script>

/* * drum-picker.js
 * ドラム式タイムピッカー
 */

const DRUM_ITEM_H = 48;
const DRUM_VISIBLE = 5;
const DRUM_CENTER = Math.floor(DRUM_VISIBLE / 2); // 2
const DRUM_REPEAT = 7; // 奇数・多めにしてループ感を出す

function _pad(n) {
    return String(n).padStart(2, '0');
}

function _initDrumColumn(col, max, init, onChange) {
    const total = max + 1;
    const baseRepeat = Math.floor(DRUM_REPEAT / 2); // 3

    let current = ((init % total) + total) % total;

    // リスト生成
    const list = document.createElement('div');
    list.className = 'drum-list drum-dragging'; // 最初はtransitionなし

    const allItems = [];
    for (let r = 0; r < DRUM_REPEAT; r++) {
        for (let v = 0; v <= max; v++) {
            const el = document.createElement('div');
            el.className = 'drum-item';
            el.textContent = _pad(v);
            list.appendChild(el);
            allItems.push({ el, value: v });
        }
    }
    col.appendChild(list);

    function calcTranslate(idx) {
        return -(baseRepeat * total + idx - DRUM_CENTER) * DRUM_ITEM_H;
    }

    function updateClasses(active) {
        const prev = ((active - 1) + total) % total;
        const next = (active + 1) % total;
        allItems.forEach(({ el, value }) => {
            el.classList.remove('drum-selected', 'drum-near');
            if (value === active) {
                el.classList.add('drum-selected');
            } else if (value === prev || value === next) {
                el.classList.add('drum-near');
            }
        });
    }

    function snapTo(idx, animate) {
        current = ((idx % total) + total) % total;
        const ty = calcTranslate(current);
        if (animate) {
            list.classList.remove('drum-dragging');
        } else {
            list.classList.add('drum-dragging');
        }
        list.style.transform = `translateY(${ty}px)`;
        updateClasses(current);
        onChange(current);
    }

    // DOMに追加されてからtransitionなしで初期位置をセット
    requestAnimationFrame(() => {
        snapTo(current, false); // transitionなしで確実に表示
        requestAnimationFrame(() => {
            list.classList.remove('drum-dragging'); // 次フレームでtransitionを有効に
        });
    });

    // ── ポインターイベント ──
    let isDragging = false;
    let startY = 0;
    let startTranslate = 0;
    let lastY = 0;
    let lastT = 0;
    let velocity = 0;

    col.addEventListener('pointerdown', e => {
        e.preventDefault();
        isDragging = true;
        startY = e.clientY;
        lastY = e.clientY;
        lastT = Date.now();
        velocity = 0;
        startTranslate = calcTranslate(current);
        list.classList.add('drum-dragging');
        col.setPointerCapture(e.pointerId);
    });

    col.addEventListener('pointermove', e => {
        if (!isDragging) return;
        const dy = e.clientY - startY;
        const nowT = Date.now();
        velocity = (e.clientY - lastY) / (nowT - lastT + 1) * 16;
        lastY = e.clientY;
        lastT = nowT;
        list.style.transform = `translateY(${startTranslate + dy}px)`;
    });

    col.addEventListener('pointerup', e => {
        if (!isDragging) return;
        isDragging = false;
        const dy = e.clientY - startY;
        const delta = -Math.round(dy / DRUM_ITEM_H) - Math.round(velocity / DRUM_ITEM_H * 3);
        snapTo(current + delta, true);
    });

    // ── ホイール ──
    col.addEventListener('wheel', e => {
        e.preventDefault();
        snapTo(current + (e.deltaY > 0 ? 1 : -1), true);
    }, { passive: false });

    // ── タッチ（Safari補完） ──
    let touchStartY = 0;
    col.addEventListener('touchstart', e => {
        touchStartY = e.touches[0].clientY;
    }, { passive: true });
    col.addEventListener('touchmove', e => {
        e.preventDefault();
    }, { passive: false });
    col.addEventListener('touchend', e => {
        const dy = e.changedTouches[0].clientY - touchStartY;
        snapTo(current - Math.round(dy / DRUM_ITEM_H), true);
    });

    return {
        getValue: () => current,
        setValue: v => snapTo(v, true),
    };
}

/**
 * タイムピッカー全体を初期化
 * @param {string} pickerId - drum-picker 要素の id（例: 'bedtime-picker'）
 */
function initDrumTimePicker(pickerId) {
    const now = new Date();
    const h = now.getHours();
    const m = now.getMinutes();

    const hCol   = document.getElementById(pickerId + '-h');
    const mCol   = document.getElementById(pickerId + '-m');
    const hidden = document.getElementById(pickerId + '-val');

    if (!hCol || !mCol || !hidden) {
        console.warn('[drum-picker] 要素が見つかりません。id確認:', pickerId);
        console.warn('  探したid:', pickerId + '-h', '/', pickerId + '-m', '/', pickerId + '-val');
        return;
    }

    function sync() {
        hidden.value = _pad(hCtrl.getValue()) + ':' + _pad(mCtrl.getValue());
    }

    const hCtrl = _initDrumColumn(hCol, 23, h, sync);
    const mCtrl = _initDrumColumn(mCol, 59, m, sync);

    // hidden の初期値セット（rAF後なので少し遅延）
    setTimeout(sync, 50);
}

document.addEventListener('DOMContentLoaded', () => {
    initDrumTimePicker('bedtime-picker');
    initDrumTimePicker('wakeup-picker');
});
    </script>
</body>
</html>