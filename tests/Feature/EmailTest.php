<?php

namespace Tests\Feature;

use App\Jobs\SendEmails;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailTest extends TestCase
{
    // use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testValidSendEmail()
    {
        $response = $this->post('/api/login', [
            'email' => 'kumarajith1996+test@gmail.com',
            'password' => 'test'
        ]);
        $token = $response->json('token');

        Queue::fake();
        $data = [
            [
                'to' => 'kumarajith1996+test1@example.com',
                'subject' => 'Test Subject 1',
                'body' => 'Test Body 1',
            ],
            [
                'to' => 'kumarajith1996+test2@example.com',
                'subject' => 'Test Subject 2',
                'body' => 'Test Body 2',
            ],
        ];

        $count = count($data);

        $response = $this->postJson('/api/1/send?api_token=' . $token, ['data' => $data]);

        Queue::assertPushed(SendEmails::class, function ($job) use ($count) {
            return count($job->emailIds) === $count;
        });

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Emails will be sent soon']);
    }

    public function testInvalidSendEmail()
    {
        $response = $this->post('/api/login', [
            'email' => 'kumarajith1996+test@gmail.com',
            'password' => 'test'
        ]);
        $token = $response->json('token');

        Queue::fake();
        $data = [];

        $response = $this->postJson('/api/1/send?api_token=' . $token, ['data' => $data]);

        Queue::assertNotPushed(SendEmails::class);

        $response->assertStatus(422);
        
        $data = [
            [
                'to' => 'Invalid address',
                'subject' => 'Test Subject 1',
                'body' => 'Test Body 1',
            ]
        ];

        $response = $this->postJson('/api/1/send?api_token=' . $token, ['data' => $data]);

        Queue::assertNotPushed(SendEmails::class);

        $response->assertStatus(422);

        $data = [
            [
                'subject' => 'Test Subject 1',
                'body' => 'Test Body 1',
            ]
        ];

        $response = $this->postJson('/api/1/send?api_token=' . $token, ['data' => $data]);

        Queue::assertNotPushed(SendEmails::class);

        $response->assertStatus(422);
    }

    public function testListEmails()
    {
        $index = config('elasticsearch.sent_emails_index');
        \MailerLite\LaravelElasticsearch\Facade::indices()->delete(['index' => $index]);
        \MailerLite\LaravelElasticsearch\Facade::indices()->create(['index' => $index]);

        $response = $this->post('/api/login', [
            'email' => 'kumarajith1996+test@gmail.com',
            'password' => 'test'
        ]);
        $token = $response->json('token');
        Queue::fake();
        $data = [
            [
                'to' => 'kumarajith1996+test1@example.com',
                'subject' => 'Test Subject 1',
                'body' => 'Test Body 1',
            ],
            [
                'to' => 'kumarajith1996+test2@example.com',
                'subject' => 'Test Subject 2',
                'body' => 'Test Body 2',
            ],
        ];

        $response = $this->postJson('/api/1/send?api_token=' . $token, ['data' => $data]);
        sleep(5);
        $response = $this->get('/api/list/1?api_token=' . $token);
        $response->assertStatus(200)
            ->assertJsonStructure([[
                'took',
                'timed_out',
                '_shards',
                'hits',
            ]]);

        $hits = $response->json()[0]['hits']['hits'];
        $this->assertEquals($hits[0]['_source']['to'], $data[0]['to']);
        $this->assertEquals($hits[0]['_source']['subject'], $data[0]['subject']);
        $this->assertEquals($hits[0]['_source']['body'], $data[0]['body']);

        $this->assertEquals($hits[1]['_source']['to'], $data[1]['to']);
        $this->assertEquals($hits[1]['_source']['subject'], $data[1]['subject']);
        $this->assertEquals($hits[1]['_source']['body'], $data[1]['body']);
    }
}
