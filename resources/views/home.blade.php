<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSRFトークンでフォーム送信時の不正リクエスト防止-->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ホーム - SleepPal</title>
    <!-- ViteでCSS/JSを読み込んでいる-->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="home-page">
    <header class="site-header">
        <div class="header-left">
            <!-- route('home')に遷移するようにURL貼ってる-->
            <a href="{{ route('home') }}" class="home-icon-link">
                <img src="{{ asset('images/characters/home.png') }}" alt="ホーム" class="home-icon">
            </a>
        </div>
        <div class="header-center">
            <h1 class="site-logo">SleePal</h1>
        </div>
        <div class="header-right">
            <span class="user-name">{{ auth()->user()->name }}</span>
            <!--POSTメソッドでログアウト処理を実行-->
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        </div>
    </header>

    <div class="main-layout">
        <aside class="decoration-sidebar decoration-sidebar-left">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
        </aside>

        <main class="main-content">
            <!--キャラクター表示してる場所-->
            <div class="character-display">

            @php
            // レベルを1~3の範囲に強制している。levelがnullなら1,4以上なら3になる
            //発表用にレベルの数は少なめ
            $level = $character->level ?? 1;
            $level = max(1, min(3, $level));

            if (auth()->user()->characterType && auth()->user()->characterType->image_path) {
                $basePath = auth()->user()->characterType->image_path;

                if ($level === 1) {
                    $imagePath = $basePath; // lv1はそのまま画像を持ってきている
                } elseif ($level === 2) {
                    //正規表現で拡張子の直前に "_lv2" を挿入
                    // 例： "images/imo.png" => "images/imo_lv2.png"
                    $imagePath = preg_replace('/(\.\w+)$/', '_lv2$1', $basePath);
                } else {
                    $imagePath = preg_replace('/(\.\w+)$/', '_lv3$1', $basePath);
                }
            } else {
                $imagePath = "images/characters/default_lv{$level}.png";
            }
            @endphp
                <!--キャラクター画像-->
                <div class="character-box">
                    <!--asset()で画像のURLを生成して表示-->
                    <img
                        src="{{ asset($imagePath) }}"
                        alt="{{ $character->name }}"
                        class="character-image"
                    >
                </div>

                <!--キャラクター名・レベルの情報-->
                <div class="character-info">
                    <!--レベルとキャラクター名-->
                    <h2 class="character-name">
                        Lv.{{ $character->level }} {{ $character->name }}
                    </h2>

                    <!-- //次のレベルに必要な合計分かれ目のポイント -->
                    @php
                        $nextThresholds = [1 => 20, 2 => 30];

                        //pointsがnullの場合は0を入れる
                        $currentPoints = $character->points ?? 0;
                    @endphp
                    
                    <!-- レベルが最大の時以外進捗バーを表示 -->
                    <!-- //次のレベルに必要な合計ポイント -->
                    @if($character->level < 3)
                        @php
                            $nextRequired = $nextThresholds[$character->level];

                            // 前のレベルとの分かれ目
                            $prevRequired = $character->level === 1 ? 0 : $nextThresholds[$character->level - 1];

                            // 残りの必要なポイント（マイナスにならないように0以上に制限している）
                            $remaining = max(0, $nextRequired - $currentPoints);
                            // プログレスバーの割合を計算している
                            // (現在のpt - 前のレベルとの分けれ目) ÷ (次のレベルの分かれ目 - 前のレベルとの分かれ目) x 100
                            $progress = $nextRequired > $prevRequired
                                ? min(100, round(($currentPoints - $prevRequired) / ($nextRequired - $prevRequired) * 100))
                                : 100;
                        @endphp
                        <div class="level-progress">
                            <div class="level-progress-label">
                                <!-- 次のレベルまでの残りのポイントと現在の合計ポイントの表示 -->
                                <span>次のレベルまで {{ $remaining }}pt</span><br>
                                <span>合計 {{$currentPoints}}pt</span>
                            </div>
                            <!-- プログレスバー -->
                            <div class="level-progress-bar-bg">
                                <!-- 進捗に応じてwidthを動かすことができる -->
                                <div class="level-progress-bar-fill" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    @else
                    <!-- 最大レベルに行くと進捗バーのところにメッセージが表示される -->
                        <div class="level-progress">
                            <div class="level-progress-label">🌟 最大レベル達成！</div>
                        </div>
                    @endif
                </div>

                <!-- 睡眠不足の警告 -->
                @if($character->status === 'warning')
                <div class="alert alert-warning">
                    <p>⚠️ 睡眠不足が続いています。このままだとキャラクターが旅に出てしまうかも...</p>
                </div>
                @elseif($character->status === 'leaving')
                <div class="alert alert-danger">
                    <p>キャラクターが旅に出てしまいました。睡眠を改善すると戻ってきます。</p>
                </div>
                @endif
            </div>

            <!-- 今日の睡眠記録がある場合のみカードを表示 -->
            @if($todayRecord)
            <div class="today-record-card">
                <h3 class="card-title">今日の睡眠記録</h3>
                <div class="stats-grid">
                    <!-- number_format()で小数点を一桁にして表示 -->
                    <div class="stat-item stat-blue">
                        <div class="stat-label">睡眠時間</div>
                        <div class="stat-value">{{ number_format($todayRecord->hours, 1) }}h</div>
                    </div>
                    <div class="stat-item stat-green">
                        <div class="stat-label">獲得ポイント</div>
                        <div class="stat-value">{{ $todayRecord->points_earned }}pt</div>
                    </div>
                    <!-- substr()で時刻の文字列のHH:MMだけ切り出して表示 -->
                    <div class="stat-item stat-purple">
                        <div class="stat-label">就寝時刻</div>
                        <div class="stat-value">{{ substr($todayRecord->bedtime, 0, 5) }}</div>
                    </div>
                    <div class="stat-item stat-orange">
                        <div class="stat-label">起床時刻</div>
                        <div class="stat-value">{{ substr($todayRecord->wakeup, 0, 5) }}</div>
                    </div>
                </div>
                @if($todayRecord->memo)
                <div class="memo-box">
                    <div class="memo-label">メモ</div>
                    <div class="memo-text">{{ $todayRecord->memo }}</div>
                </div>
                @endif
                <div class="sidebar-card">
                    <h3 class="sidebar-title">過去の睡眠記録</h3>
                    <div class="sidebar-item">
                        <a href="{{ route('sleep.index') }}" class="sidebar-link">記録を見る</a>
                    </div>
                </div>
            </div>
            @endif

            <div class="action-buttons">
                <a href="{{ route('sleep.create') }}" class="action-btn btn-sleep">
                    <div class="btn-text">睡眠を記録する</div>
                </a>
                <a href="{{ route('shop.index') }}" class="action-btn btn-shop">
                    <div class="btn-text">ショップ</div>
                </a>
            </div>
        </main>

        <!-- 右側の雲 -->
        <aside class="decoration-sidebar">
            <div class="cloud cloud-1"></div>
            <div class="cloud cloud-2"></div>
            <div class="cloud cloud-3"></div>
        </aside>
    </div>
</body>
</html>