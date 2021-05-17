<?php

namespace App\Console\Commands;

use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class CreateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建es的索引';

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
        $client = ClientBuilder::create()->build();
        $params = [
            "index" => "job_test",
            "body" => [
                "settings" => [
                    "number_of_shards" => 2,
                    "number_of_replicas" => 0
                ],
                "mappings" => [
                    "properties" => [
                        "suggest" => [
                            "type" => "completion",
                            "analyzer"=> "ik_max_word"
                        ],
                        "wxid" => [
                            "type" => "keyword"
                        ],
                        "msg_type" => [
                            "type" => "keyword"
                        ],
                        "send_wxid" => [
                            "type" => "keyword"
                        ],
                        "send_sender" => [
                            "type" => "keyword"
                        ],
                        "content" => [
                            "type" => "text",
                            "analyzer" => "ik_max_word"
                        ],
                        "add_time" => [
                            "type" => "integer"
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->indices()->create($params);
        dd($response);
    }
}
