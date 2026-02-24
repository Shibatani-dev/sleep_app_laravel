<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * usersテーブルに新しいカラムを追加
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // character_type_idカラムを追加
            // emailカラムの後に配置
            $table->foreignId('character_type_id')
                ->nullable() // NULL可能（初期状態では選択していないため）
                ->after('email') // emailカラムの次に配置
                ->constrained('character_types') // character_typesテーブルと紐付け（外部キー制約）
                ->onDelete('set null'); // character_typesが削除されたらNULLにする
            
            // キャラクター選択済みフラグ
            $table->boolean('character_selected')
                ->default(false) // デフォルトはfalse（未選択）
                ->after('character_type_id');
        });
    }

    /**
     * 追加したカラムを削除
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['character_type_id']); // 外部キー制約を削除
            $table->dropColumn(['character_type_id', 'character_selected']); // カラムを削除
        });
    }
};