<?php

return [
    //'connector' => 'Sync'
    'connector' => 'redis',         // 队列驱动使用 redis 推荐， 可选 database
    'default' => 'default',        // 默认的队列名称
    'host' => '127.0.0.1',          // redis 主机地址
    'port' => 6379,                     // redis 端口
    'password' => '',             // redis 密码
    'select' => 2,                   // redis db 库, 建议显示指定 1-15 的数字均可，如果缓存驱动是 redis，避免和缓存驱动 select 冲突
    'timeout' => 0,                     // redis 超时时间
    'persistent' => false,              // redis 持续性，连接复用
];