<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Player;
use Faker\Factory as Faker;

class CoachSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('zh_TW');

        $regions = ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市', '新竹市', '嘉義市'];
        $levels = ['2.5', '3.0', '3.5', '4.0', '4.5'];
        $genders = ['男', '女'];
        $hands = ['右手', '左手'];
        $backhands = ['單反', '雙反'];
        $themes = ['gold', 'platinum', 'standard', 'sakura', 'onyx'];
        $methods = ['個人', '團體'];
        $locations = ['大安森林公園', '內湖網球中心', '彩虹河濱公園', '台中中興網球場', '高雄文化中心'];
        $tags = ['新手友善', '發球', '正拍', '反拍', '雙打', '步伐', '戰術', '比賽心理'];
        $certs = [
            '全國青少年 C 級網球教練證',
            '甲組選手資歷 / 青少年培訓經驗',
            '校隊教練 5 年 / 團體訓練規劃',
            '單打強化 / 站位與球路拆解',
            '基礎動作矯正 / 入門提升'
        ];
        $priceNotes = ['含場地費', '不含場地費', '可協助訂場'];

        $coachNames = ['林承哲', '張雅晴', '王俊豪', '陳宥廷', '李佳穎', '黃冠宇', '吳子涵', '謝欣妤'];

        foreach ($coachNames as $index => $name) {
            $user = User::firstOrCreate(
                ['line_user_id' => 'coach_seed_' . $index],
                ['name' => $name]
            );

            $player = Player::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $name,
                    'region' => $faker->randomElement($regions),
                    'level' => $faker->randomElement($levels),
                    'gender' => $faker->randomElement($genders),
                    'handed' => $faker->randomElement($hands),
                    'backhand' => $faker->randomElement($backhands),
                    'intro' => $faker->realText(80),
                    'fee' => '教練課程',
                    'theme' => $faker->randomElement($themes),
                ]
            );

            $player->update([
                'is_coach' => true,
                'coach_price_min' => $faker->numberBetween(800, 2000),
                'coach_methods' => implode(',', $faker->randomElements($methods, $faker->numberBetween(1, 2))),
                'coach_locations' => implode(',', $faker->randomElements($locations, $faker->numberBetween(1, 3))),
                'coach_tags' => implode(',', $faker->randomElements($tags, $faker->numberBetween(2, 6))),
                'coach_certs' => $faker->randomElement($certs),
                'coach_price_note' => $faker->randomElement($priceNotes),
            ]);
        }

        $extraCoaches = Player::where('is_coach', false)->inRandomOrder()->take(6)->get();
        foreach ($extraCoaches as $player) {
            $player->update([
                'is_coach' => true,
                'coach_price_min' => $faker->numberBetween(700, 1800),
                'coach_methods' => implode(',', $faker->randomElements($methods, $faker->numberBetween(1, 2))),
                'coach_locations' => implode(',', $faker->randomElements($locations, $faker->numberBetween(1, 2))),
                'coach_tags' => implode(',', $faker->randomElements($tags, $faker->numberBetween(2, 5))),
                'coach_certs' => $faker->randomElement($certs),
                'coach_price_note' => $faker->randomElement($priceNotes),
            ]);
        }
    }
}
