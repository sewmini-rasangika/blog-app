<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class GenerateSeo extends Command
{
    protected $signature = 'seo:generate {--force : make current meta title/description}';

    protected $description = 'auto generate post meta title, description, keywords and image ';

    public function handle(): int
    {
        $force = $this->option('force');

        $query = Post::withTrashed();   
        $bar = $this->output->createProgressBar($query->count());


        $query->chunkById(50, function ($posts) use ($force, $bar) {
            foreach ($posts as $post) {
                if ($force) {
                    $manual = $post->meta_manual ?? [];
                    if (! in_array('meta_title', $manual, true)) {
                        $post->meta_title = null;
                    }
                    if (! in_array('meta_description', $manual, true)) {
                        $post->meta_description = null;
                    }
                }

                $post->save();              

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info('Meta details generated ✅');

        return self::SUCCESS;
    }
}
