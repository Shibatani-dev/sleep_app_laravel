<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * 設定画面を表示
     */
    public function index()
    {
        $user = auth()->user();
        $setting = $user->setting;
        $character = $user->character;
        
        return view('settings.index', compact('setting', 'character'));
    }
    
    /**
     * 設定を更新
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'target_bedtime' => 'required',
            'target_wakeup' => 'required',
            'target_sleep_hours' => 'required|numeric|min:4|max:12',
            'reminder_enabled' => 'boolean',
        ]);
        
        $user = auth()->user();
        $setting = $user->setting;
        
        $setting->update([
            'target_bedtime' => $validated['target_bedtime'],
            'target_wakeup' => $validated['target_wakeup'],
            'target_sleep_hours' => $validated['target_sleep_hours'],
            'reminder_enabled' => $request->has('reminder_enabled'),
        ]);
        
        return redirect()->route('settings.index')->with('success', '設定を更新しました');
    }
    
    /**
     * データをリセット（危険な操作）
     */
    public function reset(Request $request)
    {
        $user = auth()->user();
        
        // 睡眠記録を全削除
        $user->sleepRecords()->delete();
        
        // キャラクターをリセット
        $character = $user->character;
        $character->update([
            'level' => 1,
            'points' => 0,
            'trust_score' => 100,
            'status' => 'normal',
            'consecutive_bad_sleep' => 0,
        ]);
        
        return redirect()->route('home')->with('success', 'データをリセットしました');
    }
}