<?php


namespace App\Services;

use Elasticsearch\ClientBuilder;

/**
 * 操作elasticsearch
 * Class EsService
 * @package App\Services
 */
class EsService
{
    /**
     * 创建索引
     */
    public static function CreateEsIndex($api_key, $api_secret) {
        $es_index = "dataai_es_index_".md5($api_key.$api_secret);
        $client = ClientBuilder::create()->build();
        // 判断索引是否存在
        if(!$client->indices()->exists(["index"=>$es_index])){
            $params = [
                "index" => $es_index,
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
            $client->indices()->create($params);
        }
    }
}
