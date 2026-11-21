<?php

namespace Tests\Feature;

use App\Models\HospitalNews;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NewsConcurrencyTest extends TestCase
{
    public function test_second_admin_update_with_stale_version_is_rejected(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => env('DB_HOST', '127.0.0.1'),
            'database.connections.mysql.port' => env('DB_PORT', '3306'),
            'database.connections.mysql.database' => 'hospitalbookingdb',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');

        $admin = User::create([
            'full_name' => 'News Concurrent Admin',
            'email' => 'news_concurrent_' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'role_id' => 1,
            'status' => 1,
        ]);

        $article = HospitalNews::create([
            'title' => 'Tin ban dau',
            'content' => 'Noi dung ban dau',
            'category' => 'Thông báo',
            'author_id' => $admin->user_id,
            'is_published' => 1,
            'published_at' => now(),
        ]);

        $article->refresh();
        $staleVersion = $article->news_version;

        try {
            $this->actingAs($admin)
                ->put(route('admin.news.update', $article->news_id), [
                    'title' => 'Nguoi thu nhat',
                    'content' => 'Noi dung nguoi thu nhat',
                    'category' => 'Thông báo',
                    'is_published' => 1,
                    'version' => $staleVersion,
                ])
                ->assertRedirect(route('admin.news.index'))
                ->assertSessionHas('success');

            $this->actingAs($admin)
                ->put(route('admin.news.update', $article->news_id), [
                    'title' => 'Nguoi thu hai ghi de',
                    'content' => 'Noi dung nguoi thu hai',
                    'category' => 'Thông báo',
                    'is_published' => 1,
                    'version' => $staleVersion,
                ])
                ->assertRedirect(route('admin.news.index'))
                ->assertSessionHas('warning')
                ->assertSessionHas('reload_page');

            $article->refresh();
            $this->assertSame('Nguoi thu nhat', $article->title);
            $this->assertSame($staleVersion + 1, $article->news_version);
        } finally {
            HospitalNews::where('news_id', $article->news_id)->delete();
            $admin->delete();
        }
    }
}
