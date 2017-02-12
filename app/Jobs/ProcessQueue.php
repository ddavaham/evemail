<?php

namespace EVEMail\Jobs;

use EVEMail\Queue;
use EVEMail\MailRecipients;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use EVEMail\Http\Controllers\EVEController;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessQueue implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $eve;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->eve = new EVEController();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $queued_ids = Queue::select('queue_id')->get();

        if (!is_null($queued_ids)) {
            $ids = [];
            foreach ($queued_ids as $id) {
                $ids[] = $id->queue_id;
            }
            $parse_ids = $this->eve->post_universe_names($ids);
            if ($parse_ids->httpStatusCode == 200) {
                foreach ($parse_ids->response as $parsed_id) {
                    MailRecipients::create([
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
