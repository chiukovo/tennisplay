<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Player;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class StressTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create('zh_TW');
        $targetUserId = 1;

        // Ensure target user exists
        if (!User::find($targetUserId)) {
            User::create([
                'id' => $targetUserId,
                'name' => 'Test User',
                'line_user_id' => 'target_user_1',
            ]);
        }

        $regions = ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市'];
        $levels = ['1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0'];
        $genders = ['男', '女'];
        $hands = ['右手', '左手'];
        $backhands = ['單反', '雙反'];

        $this->command->info('Starting stress test data generation...');

        for ($i = 0; $i < 200; $i++) {
            // 1. Create User
            $user = User::create([
                'name' => $faker->name,
                'line_user_id' => 'fake_' . $faker->uuid,
            ]);

            // 2. Create Player
            $player = Player::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'region' => $faker->randomElement($regions),
                'level' => $faker->randomElement($levels),
                'gender' => $faker->randomElement($genders),
                'handed' => $faker->randomElement($hands),
                'backhand' => $faker->randomElement($backhands),
                'intro' => $faker->realText(50),
                'fee' => '免費 (交流為主)',
                'theme' => 'standard',
                'photo' => null, // No photo for stress test
                'signature' => null,
            ]);

            // 3. Create Message to Target User
            Message::create([
                'from_user_id' => $user->id,
                'to_user_id' => $targetUserId,
                'to_player_id' => $player->id, // Assuming they are messaging about their own card or just linking it
                'content' => $faker->realText(30) . " (Stress Test #{$i})",
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
            ]);

            if (($i + 1) % 50 == 0) {
                $this->command->info("Generated " . ($i + 1) . " records...");
            }
        }

        $this->command->info('Stress test data generation completed!');
    }
}
