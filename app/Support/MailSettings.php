<?php

// ============================================================
// MailSettings
// DB එකේ තියෙන SMTP settings Laravel ගේ mail config එකට හරවනවා
// ============================================================

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;

class MailSettings
{

    public static function saved(): array
    {
        $s = Setting::map();

        $password = null;
        if (! empty($s['mail.password'])) {
            try {
                $password = Crypt::decryptString($s['mail.password']);
            } catch (\Throwable $e) {
                $password = null;   
            }
        }

        return [
            'enabled' => ($s['mail.enabled'] ?? '0') === '1',
            'host' => $s['mail.host'] ?? '',
            'port' => (int) ($s['mail.port'] ?? 587),
            'encryption' => $s['mail.encryption'] ?? 'tls',
            'username' => $s['mail.username'] ?? '',
            'password' => $password,
            'from_address' => $s['mail.from_address'] ?? '',
            'from_name' => $s['mail.from_name'] ?? '',
        ];
    }


    public static function mailer(array $v): array
    {
        return [
            'transport' => 'smtp',
            'scheme' => ($v['encryption'] ?? 'tls') === 'ssl' ? 'smtps' : 'smtp',
            'host' => $v['host'],
            'port' => (int) $v['port'],
            'username' => $v['username'] ?: null,
            'password' => $v['password'] ?: null,
            'timeout' => 10,  
        ];
    }

    public static function apply(): void
    {
        $v = self::saved();

        if (! $v['enabled'] || $v['host'] === '') {
            return;   
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => array_merge(config('mail.mailers.smtp', []), self::mailer($v)),
        ]);

        if ($v['from_address'] !== '') {
            config([
                'mail.from.address' => $v['from_address'],
                'mail.from.name' => $v['from_name'] ?: config('app.name'),
            ]);
        }
    }
}