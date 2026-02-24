<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Character;

class ShopController extends Controller
{
    /**
     * ショップ画面を表示
     */
    public function index()
    {
        $user = auth()->user();
        $character = $user->character;
        
        // 全アイテムを取得
        $items = Item::all();
        
        // ユーザーが所持しているアイテムのIDを取得
        $userItems = $character->items()->pluck('items.id');
        
        return view('shop.index', compact('items', 'character', 'userItems'));
    }

    /**
     * アイテムを購入
     */
    public function purchase(Request $request, $itemId)
    {
        $user = auth()->user();
        $character = $user->character;
        $item = Item::findOrFail($itemId);
        
        // すでに所持しているか確認
        if ($character->items()->where('item_id', $itemId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'すでに所持しています'
            ]);
        }
        
        // ポイント不足チェック
        if ($character->points < $item->price) {
            return response()->json([
                'success' => false,
                'message' => 'ポイントが足りません（必要: ' . $item->price . 'pt / 所持: ' . $character->points . 'pt）'
            ]);
        }
        
        // ポイント消費
        $character->points -= $item->price;
        $character->save();
        
        // アイテムを追加
        $character->items()->attach($itemId, ['equipped' => false]);
        
        return response()->json([
            'success' => true,
            'message' => $item->name . ' を購入しました！'
        ]);
    }

    /**
     * 所持アイテム一覧
     */
    public function myItems()
    {
        $user = auth()->user();
        $character = $user->character;
        
        // 所持アイテムを取得
        $ownedItems = $character->items;
        
        return view('shop.my-items', compact('ownedItems', 'character'));
    }

    /**
     * アイテムを装備/解除
     */
    public function toggleEquip($itemId)
    {
        $user = auth()->user();
        $character = $user->character;
        
        // アイテムを所持しているか確認
        $pivot = $character->items()->where('item_id', $itemId)->first();
        
        if (!$pivot) {
            return response()->json([
                'success' => false,
                'message' => 'このアイテムを所持していません'
            ]);
        }
        
        // 装備状態をトグル
        $newEquippedState = !$pivot->pivot->equipped;
        
        // 同じタイプのアイテムは1つしか装備できない
        if ($newEquippedState) {
            $item = Item::find($itemId);
            
            // 同じタイプの装備を全て外す
            $sameTypeItems = $character->items()
                ->where('type', $item->type)
                ->wherePivot('equipped', true)
                ->get();
            
            foreach ($sameTypeItems as $sameTypeItem) {
                $character->items()->updateExistingPivot($sameTypeItem->id, ['equipped' => false]);
            }
        }
        
        // 装備状態を更新
        $character->items()->updateExistingPivot($itemId, ['equipped' => $newEquippedState]);
        
        $message = $newEquippedState ? '装備しました' : '装備を解除しました';
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'equipped' => $newEquippedState
        ]);
    }
}