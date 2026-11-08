<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class ExpirePostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'posts:expire';

    /**
     * The console command description.
     */
    protected $description = 'Soft-delete all published posts whose expires_at timestamp has passed.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expired = Post::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expired as $post) {
            $post->delete(); // soft-delete → moves to trash/riwayat
            $count++;
        }

        $this->info("Expired {$count} post(s) and moved them to trash.");

        return Command::SUCCESS;
    }
}
