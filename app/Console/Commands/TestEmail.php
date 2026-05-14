<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('test:email {email?}')]
#[Description('Send a test email to verify SMTP configuration')]
class TestEmail extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'anh2482006@gmail.com';

        $this->info("Sending test email to: {$email}");

        try {
            Mail::raw('This is a test email from your Laravel application. If you received this, your Gmail SMTP configuration is working correctly!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Laravel SMTP Test - Gmail');
            });

            $this->info('✅ Test email sent successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Email failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
