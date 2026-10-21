<?php
 
namespace App\Console\Commands;
 
use App\Mail\PendingPostsReminder;
use App\Models\Post;
use App\Models\User;
use App\Support\MailSettings;
use App\Support\Panel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
 
class SendPendingPostsReminder extends Command
{
    protected $signature = 'posts:remind-pending {--days=2 : Remind about posts pending for at least this many days}';
 
    protected $description = 'Email the approvers a reminder about posts that are still waiting for approval';
 
    public function handle(): int
    {
        // Mail settings page eke switch eka on nathnam .env eke mailer eka use karanawa.
        // Eth .env eke mailer eka 'log' / 'array' nam (hari email yanne nae) nawathinawa.
        if (! MailSettings::saved()['enabled'] && in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->warn('No real mailer configured (Mail settings is off and .env MAIL_MAILER is log). No reminder sent.');
 
            return self::SUCCESS;
        }
 
        $days = max(0, (int) $this->option('days'));
 
        $posts = Post::with('user:id,name')
            ->where('status', 'pending')
            ->where('updated_at', '<=', now()->subDays($days))
            ->oldest('updated_at')
            ->get();
 
        if ($posts->isEmpty()) {
            $this->info("No posts have been pending for {$days}+ day(s).");
 
            return self::SUCCESS;
        }
 
        // Admin la + 'posts.approve' permission thiyena roles walata
        $recipients = User::query()
            ->whereNotNull('email')
            ->get()
            ->filter(fn (User $user) => $user->hasPermission('posts.approve'));
 
        if ($recipients->isEmpty()) {
            $this->warn('No users with approve permission found.');
 
            return self::SUCCESS;
        }
 
        $sent = 0;
 
        foreach ($recipients as $user) {
            try {
                Mail::to($user->email)->send(new PendingPostsReminder(
                    recipientName: $user->name,
                    posts: $posts,
                    days: $days,
                    reviewUrl: route('posts.index', ['panel' => Panel::for($user), 'status' => 'pending']),
                ));
 
                $sent++;
            } catch (\Throwable $e) {
                report($e);
                $this->error("Could not email {$user->email}: " . $e->getMessage());
            }
        }
 
        $this->info("Reminder about {$posts->count()} post(s) sent to {$sent} of {$recipients->count()} recipient(s).");
 
        return $sent > 0 ? self::SUCCESS : self::FAILURE;
    }
}