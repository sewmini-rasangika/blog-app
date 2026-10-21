<?php



namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Activity;
use App\Support\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TestMail;

class MailSettingsController extends Controller
{
    public function edit()
    {
        $saved = MailSettings::saved();
        unset($saved['password']);   

        $default = config('mail.default');

        return view('admin.settings.mail', [
            'saved' => $saved,
            'hasPassword' => ! empty(Setting::read('mail.password')),
            'activeMailer' => $default,
            'activeHost' => config("mail.mailers.{$default}.host"),
        ]);
    }

    
    public function update(Request $request)
    {
        $data = $request->validate($this->rules());

        $pairs = [
            'mail.enabled' => $request->boolean('smtp_enabled') ? '1' : '0',
            'mail.host' => $data['smtp_host'] ?? null,
            'mail.port' => $data['smtp_port'] ?? null,
            'mail.encryption' => $data['smtp_encryption'],
            'mail.username' => $data['smtp_username'] ?? null,
            'mail.from_address' => $data['smtp_from_address'] ?? null,
            'mail.from_name' => $data['smtp_from_name'] ?? null,
        ];


        if (! empty($data['smtp_password'])) {
            $pairs['mail.password'] = Crypt::encryptString($data['smtp_password']);
        } elseif ($request->boolean('clear_password')) {
            $pairs['mail.password'] = null;
        }

        Setting::write($pairs);

        Activity::log('settings.mail_updated', 'Updated mail (SMTP) settings');

        return redirect()->route('settings.mail')->with('success', 'Mail settings saved ✉️');
    }

   public function test(Request $request)
{
    $data = $request->validate($this->rules(strict: true) + ['test_to' => 'required|email']);

    $password = $data['smtp_password'] ?? MailSettings::saved()['password'];

    $config = [
        'transport' => 'smtp',
        'scheme'    => $data['smtp_encryption'] === 'ssl' ? 'smtps' : 'smtp',
        'host'      => $data['smtp_host'],
        'port'      => (int) $data['smtp_port'],
        'username'  => $data['smtp_username'] ?? null,
        'password'  => $password,
        'timeout'   => 10,
    ];

    $fromAddress = $data['smtp_from_address'];
    $fromName    = $data['smtp_from_name'] ?? config('app.name');

    try {
        Mail::build($config)->to($data['test_to'])->send(new TestMail(
            recipientName: $request->user()->name,
            fromAddress: $fromAddress,
            fromName: $fromName,
            details: [
                'SMTP host'  => $data['smtp_host'],
                'Port'       => $data['smtp_port'],
                'Encryption' => strtoupper($data['smtp_encryption']),
                'Sent at'    => now()->format('Y-m-d H:i'),
            ],
        ));
    } catch (\Throwable $e) {
        return back()
            ->withInput($request->except('smtp_password'))
            ->with('error', 'Test failed: ' . Str::limit($e->getMessage(), 300));
    }

    return back()
        ->withInput($request->except('smtp_password'))
        ->with('success', "Test email sent to {$data['test_to']}. Check your inbox (and spam folder) 📬");
}


    private function rules(bool $strict = false): array
    {
        $req = $strict ? 'required' : 'required_if:smtp_enabled,1';

        return [
            'smtp_host' => [$req, 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-]+$/'],   
            'smtp_port' => [$req, 'nullable', 'integer', 'between:1,65535'],
            'smtp_encryption' => ['required', 'in:tls,ssl,none'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => [$req, 'nullable', 'email', 'max:255'],
            'smtp_from_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}