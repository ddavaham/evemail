<?php

namespace EVEMail\Console\Commands;

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
        \EVEMail\MailBody::where('created_at', '<', \Carbon\Carbon::now()->subHours(12))->delete();
    }
}
