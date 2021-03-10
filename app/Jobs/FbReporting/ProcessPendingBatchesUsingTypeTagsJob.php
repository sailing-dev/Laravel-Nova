<?php

namespace App\Jobs\FbReporting;

use App\Services\SubmittedKeywordService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPendingBatchesUsingTypeTagsJob //implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The data to be processed.
     *
     * @var array
     */
    protected $data;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sks = new SubmittedKeywordService;
        return $sks->ProcessPendingBatchesUsingTypeTags($this->data);
    }
}
