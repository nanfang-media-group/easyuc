<?php

namespace SouthCN\EasyUC\Console;

use Illuminate\Console\Command;
use SouthCN\EasyUC\Service;

class SyncSitesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'uc:sync-sites';

    /**
     * The console command description.
     */
    protected $description = '同步站点列表';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Service::sync()->sites();
    }
}
