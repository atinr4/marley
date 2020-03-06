<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class GameStreak extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'game_streak';

    public static function getStreak($streak_count)
    {
        if ($streak_count >= 0 && $streak_count < 20) {
            $required_correct_streak = 0;
        } else if ($streak_count >= 20 && $streak_count < 49) {
            $required_correct_streak = 20;
        } else if ($streak_count >= 50 && $streak_count < 99) {
            $required_correct_streak = 50;
        } else {
            $required_correct_streak = 100;
        }

        $getStreak = GameStreak::where("required_correct_streak", $required_correct_streak)->first();
        return $getStreak->streak;
    }
}
