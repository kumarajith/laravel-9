<?php

namespace App\Jobs;

use App\Mail\UserEmail;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emailIds;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($emailIds)
    {
        $this->emailIds = $emailIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ElasticsearchHelperInterface $helper)
    {
        foreach ($this->emailIds as $id) {
            $document = $helper->getEmail($id)['_source'] ?? [];
            if (empty($document)) {
                continue;
            }

            Mail::to($document['to'])->send(new UserEmail($document));
        }
    }
}
