<?php
namespace Crx\HyperfPhpElasticsearch;

use Elasticsearch\Client;
use Hyperf\Elasticsearch\ClientBuilderFactory;

/**
 * User: crx
 * Date: 2023/6/30 0030
 * Time: 9:42:16
 */
class CrxElasticsearch
{
    /**
     * @var Client
     */
    protected Client $esClient;

    public function __construct(array $host)
    {
        $client_builder = new ClientBuilderFactory();
        $builder = $client_builder->create();
        $this->esClient = $builder->setHosts($host)->build();
    }

    /**
     * $index = 'chat_data'
     * 创建index - 相当于MySQL的数据库
     * @param string $index
     * @return array
     */
    public function createIndex(string $index): array
    {
        $params = [
            'index' => $index,
        ];
        return $this->esClient->indices()->create($params);
    }

    /**
     * $params = [
     *      'index' => 'chat_data',
     *      'body' => [
     *          '_source' => [
     *              'enabled' => true
     *          ],
     *          'properties' => [
     *              'item1' => [
     *                  'type' => 'text',
     *              ],
     *              'item2' => [
     *                  'type' => 'integer',
     *              ],
     *          ]
     *     ]
     * ];
     * 设置mapping
     * @param $params
     * @return array
     */
    public function putMapping($params): array
    {
        return $this->esClient->indices()->putMapping($params);
    }

    /**
     *  $params = [
     *      'index' => 'chat_data',
     *  ];
     * 获取mapping
     * @param $params
     * @return array
     */
    public function getMapping($params): array
    {
        return  $this->esClient->indices()->getMapping($params);
    }

    /**
     * $index = 'index_exists_'
     * 判断索引是否存在
     * @param string $index
     * @return bool
     */
    public function indexExistsEs(string $index): bool
    {
        $params = [
            'index' => $index,
        ];
        return $this->esClient->indices()->exists($params);
    }

    /**
     * $index = 'index_exists_'
     * 删除索引
     * @param string $index
     * @return array
     */
    public function deleteIndex(string $index): array
    {
        $params = [
            'index' => $index
        ];
        return $this->esClient->indices()->delete($params);
    }

    /**
     * 创建文档
     * @param array $params
     * @return array
     */
    public function indexEs(array $params): array
    {
        $indexData = [
            'index' => $params['index'],
            'body' => $params['body'],
        ];
        return $this->esClient->index($indexData);
    }

    /**
     * $params['body'] = [
     *  ['index'=>['_index'=>'lampol','_id'=>2]],
     *  ['name'=>'liming','age'=>43,'addr'=>'山东省青岛市'],
     *  ['index'=>['_index'=>'lampol','_id'=>3]],
     *  ['name'=>'郭富城','age'=>53,'addr'=>'山东省枣庄市']
     *  ];
     * 批量创建文档
     * @param array $params
     * @return array
     */
    public function bulk(array $params): array
    {
        return $this->esClient->bulk($params);
    }

    /**
     * 更新文档
     * @param array $params
     * $params = [
     *      'index' => 'chat_data',
     *       'id' => '文档id',
     *       'doc' => [
     *          '字段名1' => '要修改的值',
     *          '字段名2' => '要修改的值',
     *          '字段名3' => '要修改的值',
     *       ]
     * ]
     * @return array
     */
    public function update(array $params): array
    {
        $params = [
            'index' => $params['index'],
            'id' => $params['id'],
            'body' => [
                'doc' => $params['doc']
            ]
        ];
        return $this->esClient->update($params);
    }

    /**
     * 删除文档
     * @param $params
     * @return array
     */
    public function deleteEs($params): array
    {
        extract($params);
        $delete_data = [
            'index' => $index,
            'type' => $type,
            'id' => $id,
        ];
        return $this->esClient->delete($delete_data);
    }

    /**
     * $search = [
     *  'item1' => '1',
     *  'item2' => '2',
     *  'item3' => '3',
     * ]
     * $params['search'] = $search;
     * es搜索数据
     * @param array $params
     * @param int $page
     * @param int $size
     * @return array
     */
    public function search(array $params, int $page = 1, int $size = 10): array
    {
        $search = $params['search'];
        $params = [
            'index' => $params['index'],
            'from' => ($page <= 0) ? 0 : ($page - 1) * $size,
            'size' => $size,
            'track_total_hits' => true
        ];

        if (count($search) == 1) {
            // 搜索单个字段
            $query = [
                'match_phrase' => $search
            ];
        } else {
            // 搜索多个字段
            $must = [];
            foreach ($search as $k => $v) {
                // 假设有时间筛选，因为这里的条件类似where('xxxx','xxxx')
                if(!in_array($k,['start_time','end_time'])) {
                    $must['bool']['must'][] = ['match_phrase' => [$k => $v]];
                }
            }
            $query['bool']['must'][] = $must;

            /********* 范围搜索 *************/
            // 时间搜索
            $filter = [];
            if(!empty($search['start_time'])) {
                $filter[] = [
                    'range' => [
                        'chat_time' => [
                            'gte' => $search['start_time'],
                            'lte' => $search['end_time']
                        ],
                    ]
                ];
            }
            if (!empty($filter)) {
                $query['bool']['filter'] = $filter;
            }
        }
        // 排序
        $params['body'] = [
            'query' => $query,
            'sort' => ['chat_time'=>['order'=>'desc']]
        ];
        try {
            return $this->esClient->search($params);
        } catch (\Exception $e) {
            return [];
        }
    }

}