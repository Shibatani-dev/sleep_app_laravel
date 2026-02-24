<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Character;
use App\Models\Setting;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // キャラクター自動生成
        Character::create([
            'user_id' => $user->id,
            'name' => 'スリープメイト',
            'level' => 1,
            'points' => 0,
            'trust_score' => 100,
            'status' => 'normal',
            'consecutive_bad_sleep' => 0,
        ]);

        // 設定初期化
        Setting::create([
            'user_id' => $user->id,
            'target_bedtime' => '23:00:00',
            'target_wakeup' => '07:00:00',
            'target_sleep_hours' => 7.5,
            'reminder_enabled' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('home'));
    }
}