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

namespace Hyn\Tenancy\Tests\Generators;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Support\Carbon;

/**
 * Frozen-value tests for the tenant database password generator.
 *
 * In the separate database division mode the password is never stored, but
 * recomputed from the website on every connection. Any change to the algorithm
 * or its inputs locks every existing tenant out of its database at once.
 *
 * The expected hashes are the contract, not whatever the code returns today.
 * Should one of these fail, find out what changed rather than updating it.
 *
 * All inputs are synthetic. No production key or tenant data belongs here.
 */
class PasswordGeneratorTest extends Test
{
    /**
     * A fixed key, unrelated to any real application key.
     */
    private const KEY = 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=';

    private const WEBSITE_ID = 7;
    private const WEBSITE_UUID = '3c0f0a1e2b4d5e6f7a8b9c0d1e2f3a4b';
    private const WEBSITE_CREATED_AT = '2023-05-14 09:31:07';

    /**
     * @test
     */
    public function keyed_algorithm_is_frozen()
    {
        config(['tenancy.key' => self::KEY]);

        $this->assertSame(
            'e2794f90e67aad228c91f7e3b10ba3ec',
            app(PasswordGenerator::class)->generate($this->website()),
            'The keyed password algorithm changed. Every existing tenant just '
            . 'lost access to its database. Do not update this expectation.'
        );
    }

    /**
     * @test
     */
    public function legacy_algorithm_is_frozen()
    {
        // With no tenancy.key configured the generator falls back to hashing
        // the application key and the website id. Installations created before
        // tenancy.key existed still depend on this branch.
        config(['tenancy.key' => null, 'app.key' => self::KEY]);

        $this->assertSame(
            'fad6af9d31d525bb5de0df001a9c7bf9',
            app(PasswordGenerator::class)->generate($this->website()),
            'The legacy password algorithm changed. Installations that never '
            . 'set tenancy.key just lost access to every tenant database.'
        );
    }

    /**
     * @test
     */
    public function generation_is_deterministic()
    {
        config(['tenancy.key' => self::KEY]);

        $generator = app(PasswordGenerator::class);

        $this->assertSame(
            $generator->generate($this->website()),
            $generator->generate($this->website()),
            'The generator must return the same password for the same website '
            . 'every time; the value is never stored, only recomputed.'
        );
    }

    /**
     * @test
     */
    public function created_at_is_part_of_the_hash()
    {
        // Documents a sharp edge rather than endorsing it: the timestamp is an
        // input, so anything that changes how it is rendered as a string —
        // a date cast, a serialisation format, a data migration touching
        // created_at — invalidates the password of every affected tenant.
        config(['tenancy.key' => self::KEY]);

        $generator = app(PasswordGenerator::class);

        $moved = $this->website();
        $moved->created_at = Carbon::parse('2023-05-14 09:31:08');

        $this->assertNotSame(
            $generator->generate($this->website()),
            $generator->generate($moved),
            'created_at feeds the hash, so a one second difference must change '
            . 'the password. If this ever stops being true the algorithm changed.'
        );
    }

    /**
     * @test
     */
    public function every_input_changes_the_hash()
    {
        config(['tenancy.key' => self::KEY]);

        $generator = app(PasswordGenerator::class);
        $base = $generator->generate($this->website());

        $otherId = $this->website();
        $otherId->id = self::WEBSITE_ID + 1;

        $otherUuid = $this->website();
        $otherUuid->uuid = str_repeat('f', 32);

        $this->assertNotSame($base, $generator->generate($otherId), 'id must feed the hash');
        $this->assertNotSame($base, $generator->generate($otherUuid), 'uuid must feed the hash');

        config(['tenancy.key' => self::KEY . 'x']);
        $this->assertNotSame($base, $generator->generate($this->website()), 'the key must feed the hash');
    }

    /**
     * A website that is never persisted, so the values stay fixed.
     */
    private function website(): Website
    {
        $website = new Website();
        $website->id = self::WEBSITE_ID;
        $website->uuid = self::WEBSITE_UUID;
        $website->created_at = Carbon::parse(self::WEBSITE_CREATED_AT);

        return $website;
    }
}
