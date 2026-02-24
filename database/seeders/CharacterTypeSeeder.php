<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CharacterType;

class CharacterTypeSeeder extends Seeder
{
    /**
     * シーダー実行時に呼ばれる
     * character_typesテーブルに初期データを投入する
     */
    public function run(): void
    {
        // 1つ目のキャラクタータイプ
        CharacterType::create([
            'name' => 'ねむねむ',
            'image_path' => 'images/characters/main-character.png',
            'description' => 'おっとりとした性格で、いつも眠そう',
        ]);

        // 2つ目のキャラクタータイプ
        CharacterType::create([
            'name' => 'まるまる',
            'image_path' => 'images/characters/maruimo.png',
            'description' => '元気いっぱいで、朝が得意',
        ]);

        // 3つ目のキャラクタータイプ
        CharacterType::create([
            'name' => 'どんよん',
            'image_path' => 'images/characters/imo.png',
            'description' => '夜型で、夜更かしが好き',
        ]);
    }
}