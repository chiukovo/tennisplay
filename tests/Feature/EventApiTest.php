<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPlayer(array $userAttrs = [], array $playerAttrs = [])
    {
        $user = User::create(array_merge([
            'name' => 'Test User',
            'line_user_id' => Str::uuid()->toString(),
            'uid' => 'u'.Str::random(6),
            'gender' => null,
            'region' => null,
            'bio' => null,
            'line_picture_url' => null,
            'settings' => null,
        ], $userAttrs));

        $player = Player::create(array_merge([
            'user_id' => $user->id,
            'name' => $user->name,
            'region' => '台北市',
            'level' => '3.5',
            'gender' => '男',
            'handed' => '右手',
            'backhand' => '雙反',
            'intro' => 'intro',
            'fee' => '免費 (交流為主)',
            'theme' => 'standard',
            'is_active' => true,
            'is_verified' => false,
        ], $playerAttrs));

        return [$user, $player];
    }


    private function createEvent(User $user, Player $player, array $overrides = []): Event
    {
        return Event::create(array_merge([
            'user_id' => $user->id,
            'player_id' => $player->id,
            'region' => '台北市',
            'title' => '測試活動',
            'event_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => '台北小巨蛋',
            'address' => '台北市',
            'fee' => 100,
            'max_participants' => 4,
            'match_type' => 'doubles',
            'gender' => 'all',
            'level_min' => '3.0',
            'level_max' => '4.0',
            'notes' => '來打球',
            'status' => 'open',
        ], $overrides));
    }

    public function test_events_index_returns_paginated_list()
    {
        [$user, $player] = $this->createUserWithPlayer();
        $this->createEvent($user, $player, ['title' => 'A 活動']);
        $this->createEvent($user, $player, ['title' => 'B 活動']);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => ['id', 'title', 'location', 'region', 'fee', 'match_type']
                    ]
                ],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_event_show_returns_event_with_flags()
    {
        [$user, $player] = $this->createUserWithPlayer();
        $event = $this->createEvent($user, $player);

        $response = $this->getJson('/api/events/'.$event->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $event->id)
            ->assertJsonPath('data.title', $event->title)
            ->assertJson(['success' => true]);
    }

    public function test_event_comments_flow()
    {
        [$user, $player] = $this->createUserWithPlayer();
        $event = $this->createEvent($user, $player);

        // 未登入無法留言
        $this->postJson('/api/events/'.$event->id.'/comments', ['content' => 'hi'])
            ->assertStatus(401);

        // 登入後留言
        $this->actingAs($user, 'sanctum');
        $createRes = $this->postJson('/api/events/'.$event->id.'/comments', ['content' => '留言內容']);
        $createRes->assertStatus(200)
            ->assertJsonStructure(['comment' => ['id', 'text', 'user_id', 'user' => ['name', 'uid']]]);

        // 取得留言列表
        $listRes = $this->getJson('/api/events/'.$event->id.'/comments');
        $listRes->assertStatus(200)
            ->assertJsonFragment(['text' => '留言內容']);
    }

    public function test_event_join_and_leave()
    {
        [$organizer, $organizerPlayer] = $this->createUserWithPlayer(['name' => '主辦']);
        $event = $this->createEvent($organizer, $organizerPlayer, ['max_participants' => 1]);

        [$user, $player] = $this->createUserWithPlayer(['name' => '參加者']);

        // 未登入時 join 會被拒
        $this->postJson('/api/events/'.$event->id.'/join')
            ->assertStatus(401);

        // 登入後 join 成功
        $this->actingAs($user, 'sanctum');
        $joinRes = $this->postJson('/api/events/'.$event->id.'/join');
        $joinRes->assertStatus(200)->assertJson(['success' => true]);

        // 參加者離開
        $leaveRes = $this->postJson('/api/events/'.$event->id.'/leave');
        $leaveRes->assertStatus(200)->assertJson(['success' => true]);
    }
}
