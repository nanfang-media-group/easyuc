<?php

namespace SouthCN\EasyUC\Traits;

use Illuminate\Foundation\Auth\User;
use SouthCN\EasyUC\Repository;

trait HelpSyncUserSites
{
    protected function helpSyncUserSites(User $user, Repository $repository)
    {
        $userData = $repository->user;

        if ($userData->super()) {
            $this->syncUserAppSites($user);
        }

        if ($userData->serviceAreaAdmin()) {
            $this->syncUserServiceAreas($user, $repository->serviceAreas);
        }

        if ($userData->normalUser()) {
            $this->syncUserSites($user, $repository->sites);
        }
    }
}
