<?php

namespace EVEMail\Console\Commands;

use Carbon\Carbon;
use EVEMail\MailBody;
use Illuminate\Console\Command;

class PurgeOldMailBodies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:purge_old_mails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purges Mails older than 12 hours from the system';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        MailBody::where('created_at', '<', Carbon::now()->subHours(1))->delete();
    }
}
