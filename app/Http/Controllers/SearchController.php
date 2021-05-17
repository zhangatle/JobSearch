<?php


namespace App\Http\Controllers;


use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * 搜索首页
     */
    public function index() {
        return view("index");
    }

    /**
     * 获取搜索结果
     */
    public function search(Request $request)
    {
        $page = $request->input("page", 1);
        $keywords = $request->input("keywords", "中华");
        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'zhihu_question',
            'type' => '_doc',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $keywords,
                        'fields' => [
                            'title', 'content', 'topics'
                        ]
                    ]
                ],
                "from" => (1 - 1) * 10,
                "size" => 10,
                "highlight" => [
                    "pre_tags" => ['<span class="keyword">'],
                    "post_tags" => ['</span>'],
                    "fields" => [
                        "title" => (object)[],
                        "job_desc" => (object)[],
                        "company_name" => (object)[]
                    ]
                ]
            ]
        ];

        $response = $client->search($params);
        $hit_list = [];
        foreach ($response['hits']['hits'] as $item) {
            $source = $item["_source"];
            $item_arr = ["url"=>$source["url"],"score"=>$item["_score"],"create_date"=>$source["crawl_time"]];
            if(isset($item["highlight"]["title"])) {
                $item_arr["title"] = "".join($item["highlight"]["title"]);
            }else {
                $item_arr["title"] = $source["title"];
            }
            if(isset($item["highlight"]["content"])) {
                $item_arr["content"] = "".join($item["highlight"]["content"]);
            }else {
                $item_arr[ "content"]= $source["content"];
            }
            array_push($hit_list, $item_arr);
        }
        $total = $response["hits"]["total"]["value"];
        $res = [
            "page" => $page,
            "hit_list" => $hit_list,
            "total" => $total,
            "now_page" => $page % 10 > 0 ? ($total / 10) + 1 : ($total / 10),
            "last_seconds" => 111,
            "topn_search" => [],
            "count" => 1,
            "key_words" => $keywords,
            "job_count" => 2
        ];
        return view("result")->with($res);
    }
}
