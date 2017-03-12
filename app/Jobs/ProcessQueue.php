<?php

namespace EVEMail\Jobs;

use EVEMail\Queue;
use EVEMail\MailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use EVEMail\Http\Controller\HttpController;

class ProcessQueue implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $ids, $http;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ids)
    {

        $this->http = new HttpController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $queued_ids = Queue::select('queue_id')->limit(250)->get();

        if (!is_null($queued_ids) && $queued_ids->count() > 0) {
            $ids = [];
            foreach ($queued_ids as $id) {
                $ids[] = $id->queue_id;
            }
            $parse_ids = $this->http->post_universe_names($ids);

            if ($parse_ids->httpStatusCode == 200) {
                foreach ($parse_ids->response as $parsed_id) {
                    MailRecipient::create([
                        'recipient_id' => $parsed_id->id,
                        'recipient_name' => $parsed_id->name,
                        'recipient_type' => $parsed_id->category
                    ]);
                    Queue::where('queue_id', $parsed_id->id)->delete();
                }
            }
        }
    }
}
