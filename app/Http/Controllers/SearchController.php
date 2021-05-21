<?php


namespace App\Http\Controllers;


use App\Models\Customer;
use App\Models\Friend;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    /**
     * 搜索首页
     */
    public function index()
    {
        // 获取热门搜索关键词
        $top_search = Redis::zrevrangebyscore("search_keywords_set", "+inf", "-inf", ["limit" => ["offset" => 0, "count" => 5]]);
        return view("index", ["top_search" => $top_search]);
    }

    /**
     * 获取搜索结果
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $customer_id = $user->customer_id;
        if (!$customer = Customer::query()->where("id", $customer_id)->first()) {
            return ["message" => "企业不存在"];
        }
        $es_index = "dataai_es_index_" . md5($customer->api_id . $customer->api_key);
        $page = $request->input("p", 1);
        $keywords = $request->input("q", "");
        if ($keywords == "") {
            return view("result")->with([
                "page" => $page,
                "hit_list" => [],
                "total" => 0,
                "page_nums" => 0,
                "last_seconds" => 0,
                "top_search" => Redis::zrevrangebyscore("search_keywords_set", "+inf", "-inf", ["limit" => ["offset" => 0, "count" => 5]]),
                "count" => 0,
                "key_words" => "",
            ]);
        }
        Redis::zincrby("search_keywords_set", 1, Str::limit($keywords, 10, "..."));
        $top_search = Redis::zrevrangebyscore("search_keywords_set", "+inf", "-inf", ["limit" => ["offset" => 0, "count" => 5]]);

        $client = ClientBuilder::create()->build();
        $params = [
            'index' => $es_index,
            'type' => '_doc',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'query' => $keywords,
                        'fields' => [
                            'message_content'
                        ]
                    ]
                ],
                "from" => ($page - 1) * 10,
                "size" => 10,
                "highlight" => [
                    "pre_tags" => ['<span class="keyword">'],
                    "post_tags" => ['</span>'],
                    "fields" => [
                        "message_content" => (object)[],
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
            // 此处应该借助redis提高效率
            $group = Friend::query()->where(["wxid" => $source["wxid"], "nickname" => $source["nickname"], "customer_id" => $customer_id, "friend_id" => $source["message_wxid"]])->firstOrFail();

            $group_name = $group ? $group->friend_nickname : "未知";
            $item_arr = [
                "nickname" => $source["nickname"] ?? "未知",
                "wxid" => $source["wxid"] ?? "未知",
                "message_sender" => $source["message_sender"] ?? "未知",
                "message_wxid" => $source["message_wxid"] ?? "未知",
                "message_group" => $group_name,
                "score" => $item["_score"],
                "create_date" => Carbon::parse($source['add_time'])->toDateTimeString()
            ];
            if (isset($item["highlight"]["message_content"])) {
                $item_arr["content"] = nl2br("" . join($item["highlight"]["message_content"]));
            } else {
                $item_arr["content"] = nl2br($source["message_content"]);
            }
            array_push($hit_list, $item_arr);
        }
        $total = $response["hits"]["total"]["value"];
        $res = [
            "page" => $page,
            "hit_list" => $hit_list,
            "total" => $total,
            "page_nums" => $total < 10 ? 1 : intval($total / 10),
            "last_seconds" => $last_time,
            "top_search" => $top_search,
            "count" => 1,
            "key_words" => $keywords,
        ];
        return view("result")->with($res);
    }
}
