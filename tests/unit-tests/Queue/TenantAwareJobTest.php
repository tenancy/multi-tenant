<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Queue;

use App\User;
use Illuminate\Contracts\Foundation\Application;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\WithFaker;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
    }
}

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return new MailMessage();
    }
}


class TenantAwareJobTest extends Test
{
    use WithFaker;
    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
    }
    /** @test */
    public function current_website_id_is_included_in_job_payload()
    {
        $this->activateTenant();

        Event::fake();

        $job = new TestJob();
        \dispatch($job);

        Event::assertDispatched(JobProcessed::class, function ($event) {
            return $event->job->payload()['website_id'] === $this->website->id;
        });
    }

    /** @test */
    public function current_website_id_is_included_in_notification_job_payload()
    {
        $this->activateTenant();

        Event::fake();

        $user = factory(User::class)->create();
        $user->notify(new TestNotification());

        Event::assertDispatched(JobProcessed::class, function ($event) {
            return $event->job->payload()['website_id'] === $this->website->id;
        });
    }
}
