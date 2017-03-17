<?php

namespace EVEMail\Console\Commands;


use Carbon\Carbon;
use EVEMail\Token;
use Illuminate\Http\Request;
use EVEMail\MailHeaderUpdate;
use Illuminate\Console\Command;
use EVEMail\Jobs\GetCharacterMailHeaders;
use EVEMail\Http\Controllers\MailController;

class MailHeaderUpdater extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:update_headers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates Mail Headers';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $request = new Request();
        $this->mail = new MailController($request);
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $headers = MailHeaderUpdate::where('disabled', 0)->orderby('last_header_update', 'asc')->limit(100)->get();
        if (!is_null($headers)) {
            foreach ($headers as $header) {
                $job = (new GetCharacterMailHeaders($header->character_id))
                        ->delay(Carbon::now()->addSeconds(5));
                dispatch($job);
            }
        }
    }
}
