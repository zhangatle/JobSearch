<?php

namespace App\Console\Commands;


use App\Models\Message;
use Elasticsearch\ClientBuilder;
use Illuminate\Console\Command;

class AddRecord extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步数据到es';

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
        $message = Message::query()->get();
        $client = ClientBuilder::create()->build();
        foreach ($message as $item) {
            if(is_numeric($item["content"])) {
                continue;
            }
            $suggests = $this->gen_suggest([$item->content => 10]);
            var_dump($item->id);
            $params = [
                "index" => "job_test",
                "type" => "_doc",
                "body" => [
                    "wxid" => $item->wxid,
                    "msg_type" => $item->group,
                    "send_wxid" => $item->send_wxid,
                    "send_sender" => $item->send_sender,
                    "content" => $item->content,
                    "add_time" => $item->add_time,
                    "suggest" => $suggests,
                ]
            ];
            if($item->id % 10 == 0) {
                sleep(1);
            }
            $response = $client->index($params);
        }
    }

    public function gen_suggest($params): array
    {
        $index = "job_test";
        $client = ClientBuilder::create()->build();
        $suggests = [];
        foreach ($params as $key => $value) {
            $words = $client->indices()->analyze(["index"=>$index, "body" => ["analyzer"=> "ik_max_word", "text"=>$key]]);
            $analyzed_words = [];
            foreach ($words["tokens"] as $word) {
                array_push($analyzed_words, $word["token"]);
            }
            array_push($suggests, ["input"=> $analyzed_words, "weight"=> $value]);
        }
        return $suggests;
    }
}
