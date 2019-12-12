<?php

declare(strict_types=1);

namespace wenbinye\tars\server;

class SwooleServerProperties
{
    private static $SETTINGS = [
        'reactor_num' => 'int',
        'worker_num' => 'int',
        'max_request' => 'int',
        'max_conn' => 'int',
        'max_connection' => 'int',
        'task_worker_num' => 'int',
        'task_ipc_mode' => 'int',
        'task_max_request' => 'int',
        'task_tmpdir' => 'string',
        'task_enable_coroutine' => 'bool',
        'task_use_object' => 'bool',
        'dispatch_mode' => 'int',
        'dispatch_func' => 'callable',
        'message_queue_key' => 'string',
        'daemonize' => 'bool',
        'backlog' => 'int',
        'log_file' => 'string',
        'log_level' => 'int',
        'heartbeat_check_interval' => 'int',
        'heartbeat_idle_time' => 'int',
        'open_eof_check' => 'bool',
        'open_eof_split' => 'bool',
        'package_eof' => 'string',
        'open_length_check' => 'bool',
        'package_length_type' => 'string',
        'package_length_func' => 'callable',
        'package_max_length' => 'int',
        'open_cpu_affinity' => 'int',
        'cpu_affinity_ignore' => 'array',
        'open_tcp_nodelay' => 'bool',
        'tcp_defer_accept' => 'int',
        'ssl_cert_file' => 'string',
        'ssl_method' => 'string',
        'ssl_ciphers' => 'string',
        'user' => 'string',
        'group' => 'string',
        'chroot' => 'string',
        'pid_file' => 'string',
        'pipe_buffer_size' => 'int',
        'buffer_output_size' => 'int',
        'socket_buffer_size' => 'int',
        'enable_unsafe_event' => 'bool',
        'discard_timeout_request' => 'bool',
        'enable_reuse_port' => 'bool',
        'enable_delay_receive' => 'bool',
        'open_http_protocol' => 'bool',
        'open_http2_protocol' => 'bool',
        'open_websocket_protocol' => 'bool',
        'open_mqtt_protocol' => 'bool',
        'open_websocket_close_frame' => 'bool',
        'reload_async' => 'bool',
        'tcp_fastopen' => 'bool',
        'request_slowlog_file' => 'string',
        'enable_coroutine' => 'bool',
        'max_coroutine' => 'int',
        'ssl_verify_peer' => 'bool',
        'max_wait_time' => 'int',
    ];

    public static function has(string $name): bool
    {
        return isset(self::$SETTINGS[$name]);
    }

    public static function getType(string $name)
    {
        if (!static::has($name)) {
            throw new \InvalidArgumentException("unknown swoole setting '$name'");
        }

        return self::$SETTINGS[$name];
    }
}
