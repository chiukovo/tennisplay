<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Player;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\EventComment;
use App\Models\PlayerComment;
use App\Models\Like;
use App\Models\Follow;
use App\Models\Message;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;

class DevelopmentSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('zh_TW');
        
        $regions = ['台北市', '新北市', '桃園市', '台中市', '台南市', '高雄市', '新竹市', '嘉義市'];
        $levels = ['1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0', '6.0'];
        $genders = ['男', '女'];
        $hands = ['右手', '左手'];
        $backhands = ['單反', '雙反'];
        $matchTypes = ['singles', 'doubles', 'mixed', 'all'];

        $this->command->info('Creating Users and Players...');

        // 1. Create a "Main User" for testing
        $mainUser = User::firstOrCreate(['id' => 1], [
            'name' => '測試管理員',
            'line_user_id' => 'test_main_user',
        ]);
        
        $mainPlayer = Player::firstOrCreate(['user_id' => $mainUser->id], [
            'name' => $mainUser->name,
            'region' => '台北市',
            'level' => '3.5',
            'gender' => '男',
            'handed' => '右手',
            'backhand' => '雙反',
            'intro' => '我是主測試帳號。',
            'fee' => '免費 (交流為主)',
            'theme' => 'platinum',
        ]);

        // 2. Create more users and players
        $otherUsers = [];
        $otherPlayers = [];
        for ($i = 0; $i < 50; $i++) {
            $user = User::create([
                'name' => $faker->name,
                'line_user_id' => 'line_' . $faker->unique()->uuid,
            ]);
            $otherUsers[] = $user;

            $player = Player::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'region' => $faker->randomElement($regions),
                'level' => $faker->randomElement($levels),
                'gender' => $faker->randomElement($genders),
                'handed' => $faker->randomElement($hands),
                'backhand' => $faker->randomElement($backhands),
                'intro' => $faker->realText(80),
                'fee' => $faker->randomElement(['免費 (交流為主)', '場租均分', '一小時 100元']),
                'theme' => $faker->randomElement(['standard', 'gold', 'platinum', 'sakura', 'onyx']),
            ]);
            $otherPlayers[] = $player;
        }

        $allUsers = array_merge([$mainUser], $otherUsers);
        $allPlayers = array_merge([$mainPlayer], $otherPlayers);

        $this->command->info('Creating Social Interactions (Likes/Follows)...');

        // 3. Likes and Follows
        // Make Main User like 10 players and follow 10 users
        foreach (array_slice($otherPlayers, 0, 10) as $p) {
            Like::create(['user_id' => $mainUser->id, 'player_id' => $p->id]);
        }
        foreach (array_slice($otherUsers, 0, 10) as $u) {
            Follow::create(['follower_id' => $mainUser->id, 'following_id' => $u->id]);
        }
        
        // Make 10 users follow Main User
        foreach (array_slice($otherUsers, 10, 10) as $u) {
            Follow::create(['follower_id' => $u->id, 'following_id' => $mainUser->id]);
        }

        // Random interactions between others
        foreach ($otherUsers as $u) {
            $randomPlayers = array_rand($otherPlayers, 3);
            foreach ((array)$randomPlayers as $pIdx) {
                Like::firstOrCreate(['user_id' => $u->id, 'player_id' => $otherPlayers[$pIdx]->id]);
            }
        }

        $this->command->info('Creating Events...');

        // 4. Events
        $eventTitles = ['網球快樂約打', '週末雙打爭霸', '夜間單打練習', '混雙交流賽', '晨間網球社'];
        $locations = ['內湖網球中心', '彩虹河濱公園', '大安森林公園', '中和網球場', '台中中興網球場'];

        for ($i = 0; $i < 30; $i++) {
            $isPast = $faker->boolean(30);
            $startDate = $isPast ? $faker->dateTimeBetween('-1 month', '-1 day') : $faker->dateTimeBetween('now', '+1 month');
            $endDate = Carbon::instance($startDate)->addHours(2);
            
            $organizer = $faker->randomElement($allUsers);
            $organizerPlayer = Player::where('user_id', $organizer->id)->first();

            $event = Event::create([
                'user_id' => $organizer->id,
                'player_id' => $organizerPlayer->id,
                'title' => $faker->randomElement($eventTitles) . " - " . $faker->city,
                'region' => $organizerPlayer->region,
                'event_date' => $startDate,
                'end_date' => $endDate,
                'location' => $faker->randomElement($locations),
                'address' => $faker->address,
                'fee' => $faker->numberBetween(0, 500),
                'max_participants' => $faker->randomElement([0, 4, 6, 8]),
                'match_type' => $faker->randomElement($matchTypes),
                'gender' => 'all',
                'level_min' => '2.0',
                'level_max' => '5.0',
                'notes' => $faker->realText(100),
                'status' => $isPast ? 'completed' : 'open',
            ]);

            // Participants
            $participantCount = $faker->numberBetween(1, 5);
            $potentialParticipants = array_filter($allUsers, fn($u) => $u->id !== $organizer->id);
            $selectedUsers = array_rand($potentialParticipants, $participantCount);
            
            foreach ((array)$selectedUsers as $uIdx) {
                $pUser = $potentialParticipants[$uIdx];
                $pPlayer = Player::where('user_id', $pUser->id)->first();
                EventParticipant::create([
                    'event_id' => $event->id,
                    'user_id' => $pUser->id,
                    'player_id' => $pPlayer->id,
                    'status' => 'confirmed',
                    'registered_at' => Carbon::instance($startDate)->subDays(2),
                ]);
            }

            // Comments on Events
            if ($faker->boolean(60)) {
                for ($j = 0; $j < 3; $j++) {
                    EventComment::create([
                        'event_id' => $event->id,
                        'user_id' => $faker->randomElement($allUsers)->id,
                        'content' => $faker->randomElement(['還有位置嗎？', '加一，謝謝！', '期待跟各位交流', '請問是紅土還是硬地？']),
                    ]);
                }
            }
        }

        $this->command->info('Creating Comments on Players...');

        // 5. Comments on Players
        foreach ($allPlayers as $p) {
            if ($faker->boolean(40)) {
                for ($j = 0; $j < 3; $j++) {
                    PlayerComment::create([
                        'player_id' => $p->id,
                        'user_id' => $faker->randomElement($allUsers)->id,
                        'content' => $faker->randomElement(['球技很好！', '很有禮貌的球友', '希望下次還有機會一起打球', '推一個！']),
                    ]);
                }
            }
        }

        $this->command->info('Creating Messages...');

        // 6. Messages
        for ($i = 0; $i < 50; $i++) {
            $from = $faker->randomElement($allUsers);
            $to = $faker->randomElement(array_filter($allUsers, fn($u) => $u->id !== $from->id));
            $toPlayer = Player::where('user_id', $to->id)->first();

            Message::create([
                'from_user_id' => $from->id,
                'to_user_id' => $to->id,
                'to_player_id' => $toPlayer->id,
                'content' => $faker->realText(40),
                'created_at' => $faker->dateTimeBetween('-1 month', 'now'),
            ]);
        }

        $this->command->info('Development data seeding completed successfully!');
    }
}
