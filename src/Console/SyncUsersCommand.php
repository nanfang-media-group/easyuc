<?php

namespace SouthCN\EasyUC\Console;

use Illuminate\Console\Command;
use SouthCN\EasyUC\Service;

class SyncUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'uc:sync-users';

    /**
     * The console command description.
     */
    protected $description = '同步用户列表';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Service::sync()->users();
    }
}
