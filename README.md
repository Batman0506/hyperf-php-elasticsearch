# hyperf-php-elasticsearch
## 介绍
php操作elasticsearch，基于hyperf框架，在hyperf框架里使用elasticsearch composer包实现的搜索引擎实验，基于elasticsearch/elasticsearch二次封装直接ORM风格查询，后期持续优化

## 版本
* "php": ">=7.4"
* "elasticsearch/elasticsearch": "^7.0.0"
* "hyperf/elasticsearch": "^2.2"

## 目录
```
|-- src
|   |-- CrxElasticsearch.php
|-- vendor
|-- composer.json
|-- composer.lock
|-- README.md
```

## 使用说明
```php
$host = [
    '链接'
]
$d = new \Crx\HyperfPhpElasticsearch\CrxElasticsearch($host);
```
## 创建index
```php
$index = 'chat_data'
$d->createIndex($index);
```
## 设置mapping
```php
$params = [
   'index' => 'chat_data',
   'body' => [
       '_source' => [
           'enabled' => true
       ],
       'properties' => [
           'item1' => [
               'type' => 'text',
           ],
           'item2' => [
               'type' => 'integer',
           ],
       ]
  ]
];
$d->putMapping($params);
```

## 获取mapping
```php
$params = [
   'index' => 'chat_data',
];
$d->getMapping($params);
```

## 判断索引是否存在
```php
$index = 'index_exists_';
$d->indexExistsEs($index);
```

## 删除索引
```php
$index = 'index_exists_'
$d->deleteIndex($index);
```

## 创建文档
```php
$params = [
    'index' => '',
    'body' => []
];
$d->indexEs($params);
```

## 批量创建文档
```php
$data = [...]; // 数据集
foreach($data as $v) {
    $params['body'][] = [
        'index' => [
            '_index' => 'chat_data',
        ]
    ];
    $params['body'][] = $v;
}
$crxElasticsearch->bulk($params);
```

## 更新文档
```php
$params = [
   'index' => 'chat_data',
    'id' => '文档id',
    'doc' => [
       '字段名1' => '要修改的值',
       '字段名2' => '要修改的值',
       '字段名3' => '要修改的值',
    ]
]
$crxElasticsearch->update($params);
```

## es搜索数据
```php
$search = [
    'item1' => '1',
    'item2' => '2',
    'item3' => '3',
];
$params['search'] = $search;
$crxElasticsearch->search($params);
```


