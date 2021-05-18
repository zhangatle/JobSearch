<?php


namespace App\Http\Controllers;


use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * 搜索首页
     */
    public function index() {
        // 获取热门搜索关键词
        $top_search = Redis::zrevrangebyscore("search_keywords_set", "+inf", "-inf", ["limit"=>["offset"=>0, "count"=>5]]);
        return view("index", ["top_search"=>$top_search]);
    }

    /**
     * 获取搜索结果
     */
    public function search(Request $request)
    {
        $page = $request->input("p", 1);
        $keywords = $request->input("q", "");
        if($keywords == "") {
            return view("result")->with([
                "page" => $page,
                "hit_list" => [],
                "total" => 0,
                "page_nums" => 0,
                "last_seconds" => 0,
                "top_search" => Redis::zrevrangebyscore("search_keywords_set", "+inf", "-inf", ["limit"=>["offset"=>0, "count"=>5]]),
                "count" => 0,
                "key_words" => "",
            ]);
        }
        Redis::zincrby("search_keywords_set", 1, Str::limit($keywords, 10,"..."));
        $top_search = Redis::zrevrangebyscore("search_keywords_set", "+inf", "-inf", ["limit"=>["offset"=>0, "count"=>5]]);

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
            "page_nums" => intval($total / 10),
            "last_seconds" => $last_time,
            "top_search" => $top_search,
            "count" => 1,
            "key_words" => $keywords,
        ];
        return view("result")->with($res);
    }
}
