<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterType extends Model
{
    use HasFactory;

    /**
     * 一括代入可能なカラム
     * これらのカラムは create() や update() で値をセットできる
     */
    protected $fillable = [
        'name',
        'image_path',
        'description',
    ];

    /**
     * このキャラクタータイプを選んでいるユーザー一覧を取得
     * 例: $characterType->users で取得可能
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * このキャラクタータイプから作られたキャラクター一覧を取得
     * 例: $characterType->characters で取得可能
     */
    public function characters()
    {
        return $this->hasMany(Character::class);
    }
}