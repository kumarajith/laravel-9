<?php

namespace App\Utilities\Contracts;

interface ElasticsearchHelperInterface {
    /**
     * Store the email's message body, subject and to address inside elasticsearch.
     *
     * @param  string  $messageBody
     * @param  string  $messageSubject
     * @param  string  $toEmailAddress
     * @return mixed - Return the id of the record inserted into Elasticsearch
     */
    public function storeEmail(string $messageBody, string $messageSubject, string $toEmailAddress): mixed;

    /**
     * Get the email's message body, subject and to address inside elasticsearch.
     *
     * @param  string  $id
     * @return mixed - Return the details of the record from Elasticsearch
     */
    public function getEmail(string $id): mixed;

    /**
     * List email message body, subject and to address inside elasticsearch.
     *
     * @param  int  $page
     * @return mixed - Return the details of records from Elasticsearch
     */
    public function listEmails($page): mixed;
}
