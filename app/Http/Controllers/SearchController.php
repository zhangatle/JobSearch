<?php


namespace App\Http\Controllers;


use Carbon\Carbon;
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
        $page = $request->input("p", 1);
        $keywords = $request->input("q", "");

        $client = ClientBuilder::create()->build();
        $params = [
            'index' => 'job_test',
            'type' => '_doc',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $keywords,
                        'fields' => [
                            'content'
                        ]
                    ]
                ],
                "from" => ($page - 1) * 10,
                "size" => 10,
                "highlight" => [
                    "pre_tags" => ['<span class="keyword">'],
                    "post_tags" => ['</span>'],
                    "fields" => [
                        "content" => (object)[],
                    ]
                ]
            ]
        ];
        $start = microtime(true);
        $response = $client->search($params);
        $last_time = microtime(true) - $start;
        $hit_list = [];
        foreach ($response['hits']['hits'] as $item) {
            $source = $item["_source"];
            $item_arr = [
                "send_sender"=>$source["send_sender"] ?? "未知",
                "send_wxid"=>$source["send_wxid"] ?? "未知",
                "score"=>$item["_score"],
                "create_date"=>Carbon::parse($source['add_time'])->toDateTimeString()
            ];
            if(isset($item["highlight"]["content"])) {
                $item_arr["content"] = nl2br("".join($item["highlight"]["content"]));
            }else {
                $item_arr[ "content"]= nl2br($source["content"]);
            }
            array_push($hit_list, $item_arr);
        }
        $total = $response["hits"]["total"]["value"];
        $res = [
            "page" => $page,
            "hit_list" => $hit_list,
            "total" => $total,
            "page_nums" => $page % 10 > 0 ? ($total / 10) + 1 : ($total / 10),
            "last_seconds" => $last_time,
            "topn_search" => [],
            "count" => 1,
            "key_words" => $keywords,
            "job_count" => 2
        ];
        return view("result")->with($res);
    }
}
