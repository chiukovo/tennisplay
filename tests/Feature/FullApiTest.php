<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\Like;
use App\Models\Message;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FullApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(bool $withPlayer = true, array $userAttrs = [], array $playerAttrs = []): array
    {
        $user = User::create(array_merge([
            'name' => 'User '.Str::random(4),
            'line_user_id' => Str::uuid()->toString(),
            'uid' => 'u'.Str::random(6),
            'gender' => null,
            'region' => null,
            'bio' => null,
            'line_picture_url' => null,
            'settings' => null,
        ], $userAttrs));

        $player = null;
        if ($withPlayer) {
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
        }

        return [$user, $player];
    }

    public function test_players_index_show_and_my_cards_store_update()
    {
        [$u1, $p1] = $this->createUser();
        [$u2, $p2] = $this->createUser(false); // 沒有卡，測試 store

        // 玩家列表
        $this->getJson('/api/players')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        // 未登入查看玩家詳情
        $this->getJson('/api/players/'.$p1->id)
            ->assertStatus(200)
            ->assertJsonPath('data.id', $p1->id)
            ->assertJsonPath('data.is_liked', false)
            ->assertJsonPath('data.is_following', false);

        // 建立卡片（需登入）
        $payload = [
            'name' => '新卡',
            'region' => '台南市',
            'level' => '3.0',
            'gender' => '女',
            'handed' => '右手',
            'backhand' => '單反',
            'intro' => 'hi',
            'fee' => '免費 (交流為主)',
            'theme' => 'standard',
        ];
        Sanctum::actingAs($u2, ['*']);
        $storeRes = $this->postJson('/api/players', $payload);
        $storeRes->assertStatus(201)->assertJson(['success' => true]);
        $playerId = $storeRes->json('data.id');
        $this->assertNotNull($playerId);
        $this->assertEquals($u2->id, Player::find($playerId)->user_id);
 
        // 我的卡片
        $this->getJson('/api/my-cards')
            ->assertStatus(200)
            ->assertJsonPath('data.0.id', $playerId);
 
        // 更新卡片
        Sanctum::actingAs($u2, ['*']);
        $this->putJson('/api/players/'.$playerId, ['intro' => 'updated'])
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.intro', 'updated');
    }

    public function test_like_and_unlike_flow()
    {
        [$owner, $ownerPlayer] = $this->createUser();
        [$user, $userPlayer] = $this->createUser();

        // 按讚
        Sanctum::actingAs($user, ['*']);
        $this->postJson('/api/like/'.$ownerPlayer->id)
            ->assertStatus(200);
 
        // 狀態應為已按讚
        $this->getJson('/api/like/status/'.$ownerPlayer->id)
            ->assertStatus(200)
            ->assertJson(['is_liked' => true]);
 
        // 取消按讚
        $this->postJson('/api/unlike/'.$ownerPlayer->id)
            ->assertStatus(200);
 
        $this->getJson('/api/like/status/'.$ownerPlayer->id)
            ->assertStatus(200)
            ->assertJson(['is_liked' => false]);

    }

    public function test_follow_and_unfollow_flow()
    {
        [$a, $pA] = $this->createUser();
        [$b, $pB] = $this->createUser();

        // 追蹤
        Sanctum::actingAs($b, ['*']);
        $this->postJson('/api/follow/'.$a->uid)
            ->assertStatus(200)
            ->assertJson(['message' => '已追蹤']);
 
        // 狀態
        $this->getJson('/api/follow/status/'.$a->uid)
            ->assertStatus(200)
            ->assertJson(['is_following' => true]);
 
        // following / followers 列表
        $this->getJson('/api/following/'.$b->uid)
            ->assertStatus(200)
            ->assertJsonFragment(['uid' => $a->uid]);
        $this->getJson('/api/followers/'.$a->uid)
            ->assertStatus(200)
            ->assertJsonFragment(['uid' => $b->uid]);
 
        // 取消追蹤
        $this->postJson('/api/unfollow/'.$a->uid)
            ->assertStatus(200)
            ->assertJson(['message' => '已取消追蹤']);
 
        $this->getJson('/api/follow/status/'.$a->uid)
            ->assertStatus(200)
            ->assertJson(['is_following' => false]);

    }

    public function test_player_comments_flow()
    {
        [$owner, $ownerPlayer] = $this->createUser();
        [$commenter, $commenterPlayer] = $this->createUser();
 
        // 未登入不可留言
        $this->postJson('/api/players/'.$ownerPlayer->id.'/comments', ['content' => 'hi'])
            ->assertStatus(401);
 
        // 登入留言
        Sanctum::actingAs($commenter, ['*']);
        $createRes = $this->postJson('/api/players/'.$ownerPlayer->id.'/comments', ['content' => '留言內容']);
        $createRes->assertStatus(200)->assertJsonStructure(['comment' => ['id', 'text']]);
        $cid = $createRes->json('comment.id');
 
        // 取得留言
        $this->getJson('/api/players/'.$ownerPlayer->id.'/comments')
            ->assertStatus(200)
            ->assertJsonFragment(['text' => '留言內容']);
 
        // 刪除留言
        $this->deleteJson('/api/players/comments/'.$cid)
            ->assertStatus(200);
    }

    public function test_messages_flow()
    {
        [$sender, $senderPlayer] = $this->createUser();
        [$receiver, $receiverPlayer] = $this->createUser();
 
        // 發送訊息
        Sanctum::actingAs($sender, ['*']);
        $sendRes = $this->postJson('/api/messages', [
            'to_user_id' => $receiver->id,
            'content' => 'Hello there',
        ]);
        $sendRes->assertStatus(201)->assertJson(['success' => true]);
        $msgId = $sendRes->json('data.id');
 
        // 收件者未讀數
        Sanctum::actingAs($receiver, ['*']);
        $this->getJson('/api/messages/unread-count')
            ->assertStatus(200)
            ->assertJsonPath('count', 1);
 
        // 聊天紀錄（會順便標記已讀）
        $this->getJson('/api/messages/chat/'.$sender->uid)
            ->assertStatus(200)
            ->assertJson(['success' => true]);
 
        // 已讀數應為 0
        $this->getJson('/api/messages/unread-count')
            ->assertStatus(200)
            ->assertJsonPath('count', 0);
 
        // 標記已讀（再調用一次）
        $this->putJson('/api/messages/'.$msgId.'/read')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
 
        // 刪除訊息（收件者可刪）
        $this->deleteJson('/api/messages/'.$msgId)
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
