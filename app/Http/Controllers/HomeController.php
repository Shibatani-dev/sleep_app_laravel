<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SleepRecord;
use App\Models\Character;
use App\Models\Setting;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * ホーム画面を表示
     */
    public function index()
    {
        $user = auth()->user();
        
        //キャラクター選択済みかチェック
        if (!$user->character_selected) {
            return redirect()->route('character.selection');
        }
        
        // キャラクターが存在しない場合は作成
        if (!$user->character) {
            Character::create([
                'user_id' => $user->id,
                'name' => '$characterName',
                'level' => 1,
                'points' => 0,
                'trust_score' => 100,
                'status' => 'normal',
                'consecutive_bad_sleep' => 0,
            ]);
        }

        // ↓ここに追加（既存キャラクターの名前を更新）
        if ($user->character && $user->characterType) {
            $user->character->update([
                'name' => $user->characterType->name
            ]);
        }
        
        // 設定が存在しない場合は作成
        if (!$user->setting) {
            Setting::create([
                'user_id' => $user->id,
                'target_bedtime' => '23:00:00',
                'target_wakeup' => '07:00:00',
                'target_sleep_hours' => 7.5,
                'reminder_enabled' => true,
            ]);
        }
        
        $character = $user->character;
                
        // 今日の睡眠記録
        $todayRecord = SleepRecord::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();
        
        // 過去7日間の睡眠記録
        $weekRecords = SleepRecord::where('user_id', $user->id)
            ->where('date', '>=', Carbon::today()->subDays(6))
            ->orderBy('date', 'asc')
            ->get();
        
        // 平均値計算
        $avgSleep = $weekRecords->count() > 0
            ? round($weekRecords->avg('hours'), 1)
            : 0;
        
        $avgScore = $weekRecords->count() > 0
            ? round($weekRecords->avg('trust_score'), 0)
            : 0;
        
        // 連続記録日数
        $consecutiveDays = $this->getConsecutiveDays($user->id);
        
        return view('home', compact(
            'character',
            'todayRecord',
            'weekRecords',
            'avgSleep',
            'avgScore',
            'consecutiveDays'
        ));
    }
    
    /**
     * 連続記録日数を計算
     */
    private function getConsecutiveDays($userId)
    {
        $records = SleepRecord::where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->toArray();
        
        if (empty($records)) {
            return 0;
        }
        
        $consecutive = 1;
        
        for ($i = 0; $i < count($records) - 1; $i++) {
            $current = Carbon::parse($records[$i]);
            $next = Carbon::parse($records[$i + 1]);
            
            if ($current->diffInDays($next) === 1) {
                $consecutive++;
            } else {
                break;
            }
        }
        
        return $consecutive;
    }
}