<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // 帽子系
            [
                'name' => '王冠',
                'price' => 200,
                'type' => 'hat',
                'image_path' => 'images/items/crown.png',
            ],
            [
                'name' => 'すいみん検定1級',
                'price' => 50,
                'type' => 'card',
                'image_path' => 'images/items/card.png',
            ],
            [
                'name' => 'おやすみできたねバッジ',
                'price' => 50,
                'type' => 'badge',
                'image_path' => 'images/items/badge.png',
            ],
            
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}