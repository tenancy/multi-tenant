<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests\Isolation;

use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Traits\InteractsWithIsolation;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Mockery;

/**
 * Tenant isolation across jobs sharing a queue worker.
 *
 * A worker handles one job after another in the same memory, with nothing
 * tearing the state down in between as a request would.
 *
 * These drive the JobProcessing event, the path a worker takes. The
 * dispatch_sync tests exercise the bus middleware, a different one.
 */
class QueueIsolationTest extends Test
{
    use InteractsWithIsolation;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpIsolation();
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * Simulate a worker picking up a job, exactly as QueueProvider sees it.
     */
    private function workerPicksUp(?int $websiteId): Job
    {
        $payload = ['job' => 'stub', 'data' => []];

        if ($websiteId !== null) {
            $payload['website_id'] = $websiteId;
        }

        $job = Mockery::mock(Job::class);
        $job->shouldReceive('payload')->andReturn($payload);
        $job->shouldReceive('getConnectionName')->andReturn('database');
        $job->shouldReceive('resolveName')->andReturn('StubJob');

        event(new JobProcessing('database', $job));

        return $job;
    }

    private function workerFinishes(Job $job): void
    {
        event(new JobProcessed('database', $job));
    }

    /**
     * @test
     */
    public function a_job_carrying_a_website_id_activates_that_tenant()
    {
        $this->workerPicksUp($this->tenantA->id);

        $this->assertOnlySees(self::MARKER_A, 'job declared website_id of tenant A');
    }

    /**
     * @test
     */
    public function consecutive_tenant_jobs_do_not_bleed_into_each_other()
    {
        $this->workerPicksUp($this->tenantA->id);
        $this->assertOnlySees(self::MARKER_A, 'first job, tenant A');

        $this->workerPicksUp($this->tenantB->id);
        $this->assertOnlySees(self::MARKER_B, 'second job, tenant B, same worker');

        $this->workerPicksUp($this->tenantA->id);
        $this->assertOnlySees(self::MARKER_A, 'third job, back to tenant A');
    }

    /**
     * @test
     */
    public function a_system_job_after_a_tenant_job_does_not_inherit_the_tenant()
    {
        // A job with no website_id is a system job: a scheduled command, a
        // cleanup task. With the tenant still active it reads and writes the
        // previous customer's database.
        $this->workerPicksUp($this->tenantA->id);
        $this->assertOnlySees(self::MARKER_A, 'tenant job ran first');

        $this->workerPicksUp(null);

        $this->assertNull(
            app(Environment::class)->tenant(),
            'TENANT ISOLATION FAILURE: a job with no website_id was picked up while '
            .'tenant '.$this->tenantA->uuid.' was still active. Whatever that job '
            .'touches lands in the previous tenant\'s database.'
        );
    }

    /**
     * @test
     */
    public function a_system_job_after_a_tenant_job_cannot_read_the_tenants_rows()
    {
        // The same defect stated as consequence rather than as state.
        $this->workerPicksUp($this->tenantA->id);
        $this->workerPicksUp(null);

        try {
            $seen = $this->visibleMarkers();
        } catch (\Throwable $e) {
            // Refusing to connect is correct: no tenant should be active.
            $this->assertTrue(true);

            return;
        }

        $this->fail(
            'TENANT ISOLATION FAILURE: a job with no website_id read ['
            .implode(', ', $seen).'] from the tenant connection left behind by '
            .'the previous job.'
        );
    }

    /**
     * @test
     */
    public function a_finished_job_releases_its_tenant()
    {
        // A worker that has finished a job holds no customer's connection while
        // it waits for the next one.
        $job = $this->workerPicksUp($this->tenantA->id);
        $this->assertOnlySees(self::MARKER_A, 'job running');

        $this->workerFinishes($job);

        $this->assertNull(
            app(Environment::class)->tenant(),
            'TENANT ISOLATION FAILURE: the worker still holds tenant '
            .$this->tenantA->uuid.' after the job finished. An idle worker '
            .'should hold no tenant at all.'
        );
    }

    /**
     * @test
     */
    public function a_job_that_throws_releases_its_tenant()
    {
        // Failure paths are where cleanup gets forgotten, and a worker keeps
        // going after a job throws.
        $job = $this->workerPicksUp($this->tenantA->id);

        event(new JobExceptionOccurred('database', $job, new \RuntimeException('boom')));

        $this->assertNull(
            app(Environment::class)->tenant(),
            'TENANT ISOLATION FAILURE: tenant '.$this->tenantA->uuid.' is still '
            .'active after the job threw. The next job inherits it.'
        );
    }

    /**
     * @test
     */
    public function a_job_naming_a_website_that_no_longer_exists_does_not_inherit_the_previous_tenant()
    {
        // Websites get deleted while their jobs are still queued. Resolving
        // nothing must not silently mean "keep whatever was active".
        $this->workerPicksUp($this->tenantA->id);

        $this->workerPicksUp(999999);

        $this->assertNull(
            app(Environment::class)->tenant(),
            'TENANT ISOLATION FAILURE: a job referencing a website that does not '
            .'exist left tenant '.$this->tenantA->uuid.' active.'
        );
    }
}
