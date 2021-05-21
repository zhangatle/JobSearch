<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * 首页接口
     */
    public function index()
    {
        return view("index");
    }

    /**
     * 获取搜索建议
     */
    public function suggest(Request $request) {
        $keywords = $request->input("s", "");
        $suggest_list = [];
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'job_test',
            'type' => '_doc',
            "body" => [
                "suggest" => [
                    "my-suggest" => [
                        "text" => $keywords,
                        "completion" => [
                            "field" => "suggest",
                            "size" => 10,
//                            "fuzzy" => [
//                                "fuzziness"=> 5,
//                            ]
                        ]
                    ]
                ]
            ]
        ];
        $suggestions = $client->search($params);
        $suggestions = $suggestions["suggest"]["my-suggest"][0]["options"];
        foreach ($suggestions as $item) {
            $source = $item["_source"];
            array_push($suggest_list,Str::limit($source["content"], $limit = 80, $end = '...'));
        }
        return $suggest_list;
    }

    /**
     * 存储记录
     */
    public function store(MessageRequest $request) {
        $client = ClientBuilder::create()->build();

        $api_id = $request->input("api_id","");
        $api_key = $request->input("api_key","");

        $content_json = $request->input("content_json", []);

        $nickname = $content_json["nickname"];
        $wxid = $content_json["wxid"];
        $message_msg_type = $content_json["message"]["msg_type"];
        $message_wxid = $content_json["message"]["wxid"];
        $message_sender = $content_json["message"]["sender"];
        $message_content = $content_json["message"]["content"];


        $es_index = "dataai_es_index_".md5($api_id.$api_key);
        // 判断索引是否存在(如果不存在，则可以直接判定企业不存在)
        if(!$client->indices()->exists(["index"=> $es_index])){
            return ["message"=> "false"];
        }

        $suggests = $this->gen_suggest($es_index, [$message_content=>10]);
        $params = [
            "index" => $es_index,
            "type" => "_doc",
            "body" => [
                "nickname" => $nickname,
                "wxid" => $wxid,
                "message_msg_type" => $message_msg_type,
                "message_wxid" => $message_wxid,
                "message_sender" => $message_sender,
                "message_content" => $message_content,
                "add_time" => Carbon::now()->timestamp,
                "suggest" => $suggests,
            ]
        ];
        $response = $client->index($params);
        return [
            "status" => 0,
            "message" => "success"
        ];
    }

    /**
     * 生成搜索建议
     * @param $es_index
     * @param $params
     * @return array
     */
    public function gen_suggest($es_index , $params) {
        $client = ClientBuilder::create()->build();
        $suggests = [];
        foreach ($params as $key => $value) {
            $words = $client->indices()->analyze(["index"=>$es_index, "body" => ["analyzer"=> "ik_max_word", "text"=>$key]]);
            $analyzed_words = [];
            foreach ($words["tokens"] as $word) {
                array_push($analyzed_words, $word["token"]);
            }
            array_push($suggests, ["input"=> $analyzed_words, "weight"=> $value]);
        }
        return $suggests;
    }
}
