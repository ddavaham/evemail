<?php

namespace EVEMail\Console\Commands;

use DB;
use Log;
use Carbon\Carbon;
use EVEMail\Token;
use EVEMail\Jobs\GetCharacterMailHeaders;
use EVEMail\MailHeaderUpdate;
use EVEMail\Http\Controllers\MailController;
use EVEMail\Http\Controllers\EVEController;
use Illuminate\Console\Command;

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
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $headers = MailHeaderUpdate::orderby('last_header_update', 'asc')->limit(10)->get();
        if (!is_null($headers)) {
            foreach ($headers as $header) {
                $token = Token::where('character_id', $header->character_id)->first();
                if (!is_null($token)) {
                    $job = (new GetCharacterMailHeaders($token))
                            ->delay(Carbon::now()->addSeconds(5));
                    dispatch($job);
                }
            }
        }
    }
}
