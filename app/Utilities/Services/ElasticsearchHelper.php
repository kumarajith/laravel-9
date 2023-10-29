<?php
namespace App\Utilities\Services;

use Illuminate\Support\Facades\Auth;
use MailerLite\LaravelElasticsearch\Facade as Elasticsearch;
use App\Utilities\Contracts\ElasticsearchHelperInterface;

class ElasticsearchHelper implements ElasticsearchHelperInterface
{
    const PAGE_SIZE = 10;

    /**
     * Store the email's message body, subject and to address inside elasticsearch.
     *
     * @param  string  $messageBody
     * @param  string  $messageSubject
     * @param  string  $toEmailAddress
     * @return mixed - Return the id of the record inserted into Elasticsearch
     */
    public function storeEmail(string $messageBody, string $messageSubject, string $toEmailAddress): mixed
    {
        $index = config('elasticsearch.sent_emails_index');
        // Should be present already. Creating just in case it doesn't exist
        if (!Elasticsearch::indices()->exists(['index' => $index])) {
            Elasticsearch::indices()->create(['index' => $index]);
        }

        $data = [
            'body' => [
                'from' => Auth::user()->email,
                'name' => Auth::user()->name,
                'to' => $toEmailAddress,
                'subject' => $messageSubject,
                'body' => $messageBody,
                'time' => new \DateTime(),
            ],
            'index' => $index
        ];

        $document = Elasticsearch::index($data);
        
        return $document['_id'];
    }

    /**
     * Get the email's message body, subject and to address inside elasticsearch.
     *
     * @param  string  $id
     * @return mixed - Return the details of the record from Elasticsearch
     */
    public function getEmail($id): mixed
    {
        $index = config('elasticsearch.sent_emails_index');
        return Elasticsearch::get(['index' => $index, 'id' => $id]);
    }

    /**
     * List email message body, subject and to address inside elasticsearch.
     *
     * @param  int  $page
     * @return mixed - Return the details of records from Elasticsearch
     */
    public function listEmails($page): array
    {
        $index = config('elasticsearch.sent_emails_index');
        return Elasticsearch::search([
            'index' => $index,
            'body' => [
                'size' => self::PAGE_SIZE,
                'from' => ($page - 1) * self::PAGE_SIZE
            ],
        ]);
    }
}