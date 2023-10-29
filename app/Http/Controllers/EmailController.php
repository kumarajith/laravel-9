<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmails;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Send emails
     *
     * @param \Illuminate\Http\Request $request 
     * @return \Illuminate\Http\Response $response
     */
    public function send(Request $request)
    {
        $this->validate($request, [
            'data' => 'required|array',
            'data.*.to' => 'required|email',
            'data.*.subject' => 'required|string',
            'data.*.body' => 'required|string',
        ]);

        $data = $request->input('data');

        $elasticsearchHelper = app()->make(ElasticsearchHelperInterface::class);
        $redisHelper = app()->make(RedisHelperInterface::class);
        $elasticIds = [];
        foreach ($data as $emailToSend) {
            $elasticId = $elasticsearchHelper->storeEmail($emailToSend['body'], $emailToSend['subject'], $emailToSend['to']);
            $redisHelper->storeRecentMessage($elasticId, $emailToSend['subject'], $emailToSend['to']);
            $elasticIds[] = $elasticId;
        }

        $sendEmailsJob = SendEmails::dispatch($elasticIds)->onConnection('redis');

        return response()->json([
            'message' => 'Emails will be sent soon'
        ], 200);
    }

    /**
     * List sent emails
     *
     * @param \Illuminate\Http\Request $request
     * @param int $page
     * 
     * @return \Illuminate\Http\Response $response
     */
    public function list(Request $request, int $page)
    {
        $elasticsearchHelper = app()->make(ElasticsearchHelperInterface::class);

        return response()->json([$elasticsearchHelper->listEmails($page)],200);
    }
}
