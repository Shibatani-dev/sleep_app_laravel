<?php

namespace App\Http\Controllers;

use App\Models\SleepRecord;
use App\Models\Character;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SleepRecordController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $records = SleepRecord::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->paginate(15);
        return view('sleep.index', compact('records'));
    }

    public function create()
    {
        return view('sleep.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'    => 'required|date|before_or_equal:today', // ① 未来日付の記録を拒否
            'bedtime' => 'required|date_format:H:i',            // ② 時刻形式を厳密にチェック
            'wakeup'  => 'required|date_format:H:i',
            'memo'    => 'nullable|string|max:500|not_regex:/<[^>]*>/',
        ]);

        $user = auth()->user();

        //同じ日付に複数回記録してポイントを重複取得するのを防ぐ
        $alreadyExists = SleepRecord::where('user_id', $user->id)
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($alreadyExists) {
            return back()->withErrors(['date' => 'この日の睡眠記録はすでに存在します'])->withInput();
        }

        // 入力された日付をCarbonで日付を取得
        $dateOnly = Carbon::parse($validated['date'])->format('Y-m-d');
        // 日付＋就寝時刻で日時オブジェクトを作る
        $bedtime  = Carbon::parse($dateOnly . ' ' . $validated['bedtime']);
        // 日付+起床時刻で日時オブジェクトを作る
        $wakeup   = Carbon::parse($dateOnly . ' ' . $validated['wakeup']);

        //起床時刻が就寝時刻以下かチェック
        if ($wakeup->lte($bedtime)) {
            $wakeup->addDay();
        }


        // 睡眠時間の計算
        // diffInMinutes()ー２つの日時の差を「分」で取得
        ///60 時間に変換
        // 480分÷60 = 8時間
        $hours = $bedtime->diffInMinutes($wakeup) / 60;

        // 睡眠時間が異常値（12時間超）の場合は拒否
        if ($hours > 12) {
            return back()->withErrors(['wakeup' => '睡眠時間が不正な値です'])->withInput();
        }

        $inputTime  = now();
        $trustScore = $this->calculateTrustScore($wakeup, $inputTime, $hours, $user->id, $bedtime, $validated['date']);

        // ⑤ ポイントはサーバー側のロジックのみで計算（フロントから受け取らない）
        $pointsEarned = $this->calculatePoints($hours, $trustScore);

        SleepRecord::create([
            'user_id'       => $user->id,
            'date'          => $validated['date'],
            'bedtime'       => $validated['bedtime'],
            'wakeup'        => $validated['wakeup'],
            'hours'         => round($hours, 2),
            'memo'          => $validated['memo'],
            'input_time'    => $inputTime,
            'trust_score'   => $trustScore,
            'points_earned' => $pointsEarned,
        ]);

        $this->updateCharacter($user->character, $hours, $pointsEarned, $trustScore);

        return redirect()->route('home')->with('success', '睡眠記録を保存しました！+' . $pointsEarned . 'ポイント獲得');
    }

    public function edit($id)
    {
        //  自分のレコードのみ取得（他人のidを指定されても404になる）
        $record = SleepRecord::where('user_id', auth()->id())->findOrFail($id);
        return response()->json([
            'date'    => $record->date,
            'bedtime' => $record->bedtime,
            'wakeup'  => $record->wakeup,
            'memo'    => $record->memo,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date'    => 'required|date|before_or_equal:today', // ① 未来日付を拒否
            'bedtime' => 'required|date_format:H:i',            // ② 時刻形式を厳密にチェック
            'wakeup'  => 'required|date_format:H:i',
            'memo'    => 'nullable|string|max:500|not_regex:/<[^>]*>/',
        ]);

        // 自分のレコードのみ更新可能（他人のidを指定されても404になる）
        $record    = SleepRecord::where('user_id', auth()->id())->findOrFail($id);
        $character = auth()->user()->character;
        $editPenalty = 15;

        // 古いポイントを取り消し
        $character->points = max(0, $character->points - $record->points_earned);
        $bedtime = substr($validated['bedtime'], 0, 5);
        $wakeup  = substr($validated['wakeup'], 0, 5);

        $dateObj = Carbon::parse($validated['date'])->startOfDay();
        list($bedHour, $bedMinute)   = explode(':', $bedtime);
        list($wakeHour, $wakeMinute) = explode(':', $wakeup);

        $bedtimeCarbon  = $dateObj->copy()->setTime((int)$bedHour, (int)$bedMinute, 0);
        $wakeupCarbon   = $dateObj->copy()->setTime((int)$wakeHour, (int)$wakeMinute, 0);

        if ($wakeupCarbon->lte($bedtimeCarbon)) {
            $wakeupCarbon->addDay();
        }

        $hours = $bedtimeCarbon->diffInMinutes($wakeupCarbon) / 60;

        // ④ 睡眠時間が異常値（24時間超）の場合は拒否
        if ($hours > 24) {
            return back()->withErrors(['wakeup' => '睡眠時間が不正な値です'])->withInput();
        }

        // 値とれていないときにdie dump使うといいデバック
        // dd($hours);

        $newTrustScore = max(0, $record->trust_score - $editPenalty);

        //ポイントはサーバー側のロジックのみで再計算（フロントから受け取らない）
        $newPoints = $this->calculatePoints($hours, $newTrustScore);

        $record->update([
            'date'          => $validated['date'],
            'bedtime'       => $bedtime,
            'wakeup'        => $wakeup,
            'hours'         => round($hours, 2),
            'memo'          => $validated['memo'],
            'trust_score'   => $newTrustScore,
            'points_earned' => $newPoints,
        ]);

        // 新しいポイントを加算してレベル再計算
        $character->points += $newPoints;
        $character->recalculateLevel();
        $character->save();

        return redirect()->route('sleep.index')
            ->with('success', '睡眠記録を更新しました（信頼度-' . $editPenalty . '%）');
    }

    public function destroy($id)
    {
        //自分のレコードのみ削除可能（他人のidを指定されても404になる）
        $record    = SleepRecord::where('user_id', auth()->id())->findOrFail($id);
        $character = auth()->user()->character;

        $character->points = max(0, $character->points - $record->points_earned);
        $character->recalculateLevel();
        $character->save();

        $record->delete();

        return redirect()->route('sleep.index')->with('success', '睡眠記録を削除しました');
    }

    private function calculateTrustScore($wakeup, $inputTime, $hours, $userId, $bedtime, $currentDate)
    {
        $score = 100;

        $hoursSinceWakeup = $wakeup->diffInHours($inputTime, false);
        if ($hoursSinceWakeup < 0) {
            $hoursSinceWakeup = abs($hoursSinceWakeup);
        }

        if ($hoursSinceWakeup > 3) {
            $score -= 20;
        } elseif ($hoursSinceWakeup > 2) {
            $score -= 10;
        }

        if ($hours > 12) {
            $score -= 20;
        } elseif ($hours < 3) {
            $score -= 10;
        }

        $yesterday = SleepRecord::where('user_id', $userId)
            ->whereDate('date', Carbon::parse($currentDate)->subDay()->toDateString())
            ->first();

        if ($yesterday) {
            $yesterdayWakeup = Carbon::parse($yesterday->date)
                ->setTimeFromTimeString($yesterday->wakeup);
            $hoursBetween = $bedtime->diffInHours($yesterdayWakeup, false);
            if ($hoursBetween < 12) {
                $score -= 15;
            }
        }

        $avgHours = SleepRecord::where('user_id', $userId)
            ->where('date', '>=', Carbon::now()->subDays(7))
            ->avg('hours');

        if ($avgHours && abs($hours - $avgHours) > 3) {
            $score -= 10;
        }

        return max(0, min(100, $score));
    }

    private function calculatePoints($hours, $trustScore)
    {
        if ($hours >= 7 && $hours <= 8) {
            $basePoints = 10;
        } elseif (($hours >= 6 && $hours < 7) || ($hours > 8 && $hours <= 9)) {
            $basePoints = 8;
        } elseif ($hours >= 5 && $hours < 6) {
            $basePoints = 5;
        } else {
            $basePoints = 0;
        }

        return max(0, round($basePoints * ($trustScore / 100)));
    }

    private function updateCharacter($character, $hours, $points, $trustScore)
    {
        $character->points += $points;
        $character->trust_score = round(($character->trust_score * 0.9) + ($trustScore * 0.1));

        if ($hours < 5) {
            $character->consecutive_bad_sleep++;
        } else {
            $character->consecutive_bad_sleep = 0;
        }

        $character->recalculateLevel();

        if ($character->consecutive_bad_sleep >= 5) {
            $character->status = 'leaving';
        } elseif ($character->consecutive_bad_sleep >= 3) {
            $character->status = 'warning';
        } else {
            $character->status = 'normal';
        }

        $character->save();
    }
}