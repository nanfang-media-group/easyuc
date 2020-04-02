<?php

namespace SouthCN\EasyUC\Tests\Repositories;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Contracts\ShouldSyncOrgs;
use SouthCN\EasyUC\Contracts\ShouldSyncServiceAreas;
use SouthCN\EasyUC\Contracts\ShouldSyncSites;
use SouthCN\EasyUC\Contracts\ShouldSyncUser;
use SouthCN\EasyUC\Contracts\ShouldSyncUserSites;
use SouthCN\EasyUC\Repositories\Data\User as UserData;
use SouthCN\EasyUC\Repositories\Sync;
use SouthCN\EasyUC\Repository;
use SouthCN\EasyUC\Tests\TestCase;

class SyncTest extends TestCase implements ShouldSyncUser, ShouldSyncUserSites, ShouldSyncServiceAreas, ShouldSyncOrgs, ShouldSyncSites
{
    protected $sync;

    protected function setUp(): void
    {
        parent::setUp();

        app()->instance('easyuc.user.handler', $this);

        $this->sync = new Sync;
    }

    public function test_users()
    {
        $this->sync->users(true);
        $this->sync->users(false);
    }

    public function test_sites()
    {
        $this->sync->sites(true);
        $this->sync->sites(false);
    }

    /**
     * @inheritDoc
     */
    public function syncUser(UserData $userData, int $operation): ?User
    {
        $this->assertObjectHasAttribute('id', $userData->data);
        $this->assertObjectHasAttribute('name', $userData->data);
        $this->assertObjectHasAttribute('email', $userData->data);
        $this->assertObjectHasAttribute('note', $userData->data);
        $this->assertObjectHasAttribute('group', $userData->data);

        $this->assertTrue(in_array($operation, [0, 1, 2]));

        return 0 == $operation ? null : new User;
    }

    /**
     * @inheritDoc
     */
    public function syncUserAppSites(User $user): void
    {
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * @inheritDoc
     */
    public function syncUserServiceAreas(User $user, Repository $repository): void
    {
        $this->assertInstanceOf(User::class, $user);
        $this->assertObjectHasAttribute('id', $repository->user->data);
        $this->assertEmpty($repository->sites->data);
    }

    /**
     * @inheritDoc
     */
    public function syncUserSites(User $user, Repository $repository): void
    {
        $this->assertInstanceOf(User::class, $user);
        $this->assertObjectHasAttribute('id', $repository->user->data);
    }

    /**
     * @inheritDoc
     */
    public function syncServiceAreas(array $serviceAreaList): void
    {
        $this->assertObjectHasAttribute('id', $serviceAreaList[0]);
        $this->assertObjectHasAttribute('name', $serviceAreaList[0]);
        $this->assertObjectHasAttribute('is_ipv6', $serviceAreaList[0]);
        $this->assertObjectHasAttribute('sort', $serviceAreaList[0]);
    }

    /**
     * @inheritDoc
     */
    public function syncOrgs(array $orgList): void
    {
        $this->assertObjectHasAttribute('operation', $orgList[0]);
        $this->assertObjectHasAttribute('id', $orgList[0]->info);
    }

    /**
     * @inheritDoc
     */
    public function syncSites(array $siteList): void
    {
        $this->assertObjectHasAttribute('operation', $siteList[0]);
        $this->assertObjectHasAttribute('id', $siteList[0]->info);
    }
}
