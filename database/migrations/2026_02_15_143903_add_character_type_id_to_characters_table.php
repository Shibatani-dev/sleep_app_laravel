<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * charactersテーブルに新しいカラムを追加
     */
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            // character_type_idカラムを追加
            $table->foreignId('character_type_id')
                ->nullable() // NULL可能
                ->after('user_id') // user_idカラムの次に配置
                ->constrained('character_types') // character_typesテーブルと紐付け
                ->onDelete('cascade'); // character_typesが削除されたら一緒に削除
        });
    }

    /**
     * 追加したカラムを削除
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropForeign(['character_type_id']);
            $table->dropColumn('character_type_id');
        });
    }
};