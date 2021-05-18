<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function store(Request $request) {
        $client = ClientBuilder::create()->build();
        $content = $request->input("content","");
        $api_key = $request->input("api_key","");
        $api_secret = $request->input("api_key","");
        $wxid = $request->input("wxid","");
        $msg_type = $request->input("msg_type","");
        $send_wxid = $request->input("send_wxid","");
        $send_sender = $request->input("send_sender","");
        $add_time = $request->input("add_time",0);
        $es_index = "dataai_es_index_".md5($api_key.$api_secret);

        $suggests = $this->gen_suggest($es_index, [$content=>10]);
        $params = [
            "index" => $es_index,
            "type" => "_doc",
            "body" => [
                "wxid" => $wxid,
                "msg_type" => $msg_type,
                "send_wxid" => $send_wxid,
                "send_sender" => $send_sender,
                "content" => $content,
                "add_time" => $add_time,
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
