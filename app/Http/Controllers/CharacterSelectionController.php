<?php

namespace App\Http\Controllers;

use App\Models\CharacterType;
use App\Models\Character;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CharacterSelectionController extends Controller
{
    /**
     * キャラクター選択画面を表示
     * 
     * 処理の流れ:
     * 1. character_typesテーブルから全てのキャラクタータイプを取得
     * 2. ビューに渡して表示
     */
    public function index()
    {
        // character_typesテーブルから全データを取得
        // SELECT * FROM character_types;
        $characterTypes = CharacterType::all();
        
        // character-selection.blade.php に $characterTypes を渡して表示
        return view('character-selection', compact('characterTypes'));
    }

    /**
     * キャラクターを選択（フォーム送信時）
     * 
     * 処理の流れ:
     * 1. フォームから送られてきたcharacter_type_idが正しいかチェック
     * 2. ログイン中のユーザーを取得
     * 3. ユーザーに選択したキャラクタータイプを紐付け
     * 4. キャラクターの実体（ゲーム進行データ）を作成
     * 5. ホーム画面にリダイレクト
     */
    public function select(Request $request)
    {
        // バリデーション: character_type_idが必須で、character_typesテーブルに存在するかチェック
        $request->validate([
            'character_type_id' => 'required|exists:character_types,id',
        ]);

        // ログイン中のユーザーを取得
        $user = Auth::user();
        
        // ユーザーテーブルを更新
        // 選択したキャラクタータイプを保存
        $user->character_type_id = $request->character_type_id;
        // キャラクター選択済みフラグをtrueに
        $user->character_selected = true;
        $user->save();
        
        // キャラクター作成
        // INSERT INTO characters (user_id, character_type_id, name, ...) VALUES (...);
        Character::create([
            'user_id' => $user->id,
            'character_type_id' => $request->character_type_id,
            'name' => 'スリープメイト',
            'level' => 1,
            'points' => 0,
            'trust_score' => 100,
            'status' => 'normal',
            'consecutive_bad_sleep' => 0,
        ]);

        // ホーム画面にリダイレクト（成功メッセージ付き）
        return redirect()->route('home')->with('success', 'キャラクターを選択しました！');
    }
}