<?php

namespace Database\Seeders;

use App\Models\Player;
use Illuminate\Database\Seeder;

class PlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $players = [
            // 台北市球友
            [
                'name' => 'Alex Chen',
                'region' => '台北市',
                'level' => '4.5',
                'handed' => '右手',
                'backhand' => '雙反',
                'gender' => '男',
                'fee' => '免費 (交流為主)',
                'intro' => '週末固定在大安森林公園練球，歡迎程度相近的球友約打！主攻底線進攻型打法。',
                'photo' => 'https://images.unsplash.com/photo-1622279457486-62dcc4a4bd13?q=80&w=400&auto=format&fit=crop',
                'theme' => 'gold',
            ],
            [
                'name' => 'Emily Wang',
                'region' => '台北市',
                'level' => '3.5',
                'handed' => '右手',
                'backhand' => '雙反',
                'gender' => '女',
                'fee' => '免費 (交流為主)',
                'intro' => '剛重拾網球一年，希望找到穩定的練球夥伴，平日傍晚有空。',
                'photo' => 'https://images.unsplash.com/photo-1595435063510-482208034433?q=80&w=400&auto=format&fit=crop',
                'theme' => 'sakura',
            ],
            [
                'name' => 'Kevin Lin',
                'region' => '台北市',
                'level' => '5.0',
                'handed' => '左手',
                'backhand' => '單反',
                'gender' => '男',
                'fee' => 'NT$300/hr',
                'intro' => '前大專甲組選手，現為教練。可陪打或指導，發球強力、網前技術好。',
                'photo' => 'https://images.unsplash.com/photo-1531315630201-bb15bbeb1663?q=80&w=400&auto=format&fit=crop',
                'theme' => 'holographic',
            ],
            
            // 新北市球友
            [
                'name' => 'Jessica Huang',
                'region' => '新北市',
                'level' => '4.0',
                'handed' => '右手',
                'backhand' => '雙反',
                'gender' => '女',
                'fee' => '免費 (交流為主)',
                'intro' => '住板橋，常在新莊運動公園打球。喜歡雙打，正在練習切球和放小球。',
                'photo' => 'https://images.unsplash.com/photo-1594381898411-846e7d193883?q=80&w=400&auto=format&fit=crop',
                'theme' => 'platinum',
            ],
            [
                'name' => 'David Wu',
                'region' => '新北市',
                'level' => '3.0',
                'handed' => '右手',
                'backhand' => '雙反',
                'gender' => '男',
                'fee' => '免費 (交流為主)',
                'intro' => '打球兩年，中間程度。希望能找穩定球友一起進步，週末有空。',
                'photo' => 'https://images.unsplash.com/photo-1546519638-68e109498ffc?q=80&w=400&auto=format&fit=crop',
                'theme' => 'standard',
            ],
            
            // 台中市球友
            [
                'name' => 'Sophia Chang',
                'region' => '台中市',
                'level' => '4.5',
                'handed' => '右手',
                'backhand' => '雙反',
                'gender' => '女',
                'fee' => 'NT$200/hr',
                'intro' => '退役選手，目前在台中地區教學。歡迎各程度球友約練，可針對弱項加強。',
                'photo' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=400&auto=format&fit=crop',
                'theme' => 'gold',
            ],
            [
                'name' => 'Michael Lee',
                'region' => '台中市',
                'level' => '3.5',
                'handed' => '右手',
                'backhand' => '單反',
                'gender' => '男',
                'fee' => '免費 (交流為主)',
                'intro' => '工程師，用打網球放鬆身心。單反愛好者，歡迎切磋！',
                'photo' => 'https://images.unsplash.com/photo-1503023345310-bd7c1de61c7d?q=80&w=400&auto=format&fit=crop',
                'theme' => 'onyx',
            ],
            
            // 高雄市球友
            [
                'name' => 'Amy Tsai',
                'region' => '高雄市',
                'level' => '2.5',
                'handed' => '左手',
                'backhand' => '雙反',
                'gender' => '女',
                'fee' => '免費 (交流為主)',
                'intro' => '剛開始學網球半年，左手持拍。希望找有耐心的球友一起練習基本功。',
                'photo' => 'https://images.unsplash.com/photo-1552374196-1ab2a1c593e8?q=80&w=400&auto=format&fit=crop',
                'theme' => 'sakura',
            ],
            [
                'name' => 'Jason Yang',
                'region' => '高雄市',
                'level' => '4.0',
                'handed' => '右手',
                'backhand' => '雙反',
                'gender' => '男',
                'fee' => '免費 (交流為主)',
                'intro' => '高雄在地球友，週末固定在中正運動場。歡迎來挑戰，輸贏請飲料！',
                'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=400&auto=format&fit=crop',
                'theme' => 'holographic',
            ],
        ];

        foreach ($players as $playerData) {
            Player::create($playerData);
        }
    }
}
