<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InstantRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Global room for single-chat mode
        \App\Models\InstantRoom::updateOrCreate(
            ['slug' => 'all'],
            ['name' => '全台', 'sort_order' => 0]
        );

        $regions = [
            '基隆市' => 'keelung',
            '台北市' => 'taipei',
            '新北市' => 'new-taipei',
            '桃園市' => 'taoyuan',
            '新竹市' => 'hsinchu-city',
            '新竹縣' => 'hsinchu-county',
            '苗栗縣' => 'miaoli',
            '台中市' => 'taichung',
            '彰化縣' => 'changhua',
            '南投縣' => 'nantou',
            '雲林縣' => 'yunlin',
            '嘉義市' => 'chiayi-city',
            '嘉義縣' => 'chiayi-county',
            '台南市' => 'tainan',
            '高雄市' => 'kaohsiung',
            '屏東縣' => 'pingtung',
            '宜蘭縣' => 'yilan',
            '花蓮縣' => 'hualien',
            '台東縣' => 'taitung',
            '澎湖縣' => 'penghu',
            '金門縣' => 'kinmen',
            '連江縣' => 'matsu',
        ];

        $order = 1;
        foreach ($regions as $name => $slug) {
            \App\Models\InstantRoom::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $order++]
            );
        }
    }
}
