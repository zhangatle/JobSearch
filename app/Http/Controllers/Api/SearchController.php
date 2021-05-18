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
        $title = $request->input("title","");
        $content = $request->input("content","");
        $content = "古月一一微羽', '嘉兴单位寻：二级房建 市政,挂资质，季度签，唯一无社保都要,单位确定，带价来[勾引][勾引][勾引]";
        $suggests = $this->gen_suggest([$content=>10]);
        $params = [
            "index" => "job_test",
            "type" => "_doc",
            "body" => [
                "wxid" => "ccd@qq.com",
                "msg_type" => "group",
                "send_wxid" => "小易",
                "send_sender" => "小易",
                "content" => $content,
                "add_time" => "1621102532",
                "suggest" => $suggests,
            ]
        ];
        var_dump($suggests);
        $response = $client->index($params);
        dd($response);
    }

    public function gen_suggest($params) {
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
