<?php

use Illuminate\Database\Seeder;

class StreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arraySeed = array(
            [
                'streak' => 'Coal',
                'required_correct_streak' => 0
            ],
            [
                'streak' => 'Spark',
                'required_correct_streak' => 20
            ],
            [
                'streak' => 'Blaze',
                'required_correct_streak' => 50
            ],
            [
                'streak' => 'Inferno',
                'required_correct_streak' => 100
            ]
        );

        DB::table('game_streak')->insert($arraySeed);
    }
}
