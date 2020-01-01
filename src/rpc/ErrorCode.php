<?php

declare(strict_types=1);

namespace wenbinye\tars\rpc;

use wenbinye\tars\support\Enum;

/**
 * Class ErrorCode.
 *
 * @property string $message
 */
class ErrorCode extends Enum
{
    // 错误码定义（需要从扩展开始规划）
    const TARS_SUCCESS = 0; // taf
    const TARS_FAILED = 1; // taf失败（通用失败）
    const TARS_MALLOC_FAILED = -1; // 内存分配失败

    const TARS_SOCKET_SET_NONBLOCK_FAILED = -1002; // socket设置非阻塞失败
    const TARS_SOCKET_SEND_FAILED = -1003; // socket发送失败
    const TARS_SOCKET_RECEIVE_FAILED = -1004; // socket接收失败
    const TARS_SOCKET_SELECT_TIMEOUT = -1005; // socket的select超时，也可以认为是svr超时
    const TARS_SOCKET_TIMEOUT = -1006; // socket超时，一般是svr后台没回包，或者seq错误
    const TARS_SOCKET_CONNECT_FAILED = -1007; // socket tcp 连接失败
    const TARS_SOCKET_CLOSED = -1008; // socket tcp 服务端连接关闭
    const TARS_SOCKET_CREATE_FAILED = -1009;

    const TARS_PUT_STRUCT_FAILED = -10009;
    const TARS_PUT_VECTOR_FAILED = -10010;
    const TARS_PUT_INT64_FAILED = -10011;
    const TARS_PUT_INT32_FAILED = -10012;
    const TARS_PUT_STRING_FAILED = -10013;
    const TARS_PUT_MAP_FAILED = -10014;
    const TARS_PUT_BOOL_FAILED = -10015;
    const TARS_PUT_FLOAT_FAILED = -10016;
    const TARS_PUT_CHAR_FAILED = -10017;
    const TARS_PUT_UINT8_FAILED = -10018;
    const TARS_PUT_SHORT_FAILED = -10019;
    const TARS_PUT_UINT16_FAILED = -10020;
    const TARS_PUT_UINT32_FAILED = -10021;
    const TARS_PUT_DOUBLE_FAILED = -10022;

    const TARS_ENCODE_FAILED = -10025;
    const TARS_DECODE_FAILED = -10026;
    const TARS_GET_INT64_FAILED = -10031;
    const TARS_GET_MAP_FAILED = -10032;
    const TARS_GET_STRUCT_FAILED = -10033;
    const TARS_GET_STRING_FAILED = -10034;
    const TARS_GET_VECTOR_FAILED = -10035;
    const TARS_GET_INT32_FAILED = -10036;
    const TARS_GET_BOOL_FAILED = -10037;
    const TARS_GET_CHAR_FAILED = -10038;
    const TARS_GET_UINT8_FAILED = -10039;
    const TARS_GET_SHORT_FAILED = -10040;
    const TARS_GET_UINT16_FAILED = -10041;
    const TARS_GET_UINT32_FAILED = -10042;
    const TARS_GET_DOUBLE_FAILED = -10043;
    const TARS_GET_FLOAT_FAILED = -10044;

    // tars服务端可能返回的错误码
    const SERVER_SUCCESS = 0; //服务器端处理成功
    const SERVER_DECODE_ERR = -1; //服务器端解码异常
    const SERVER_ENCODE_ERR = -2; //服务器端编码异常
    const SERVER_NO_FUNC_ERR = -3; //服务器端没有该函数
    const SERVER_NO_SERVANT_ERR = -4; //服务器端五该Servant对象
    const SERVER_RESET_GRID = -5; //服务器端灰度状态不一致
    const SERVER_QUEUE_TIMEOUT = -6; //服务器队列超过限制
    const SERVER_ASYNC_CALL_TIMEOUT = -7; //异步调用超时
    const SERVER_PROXY_CONNECT_ERR = -8; //proxy链接异常
    const SERVER_UNKNOWN_ERR = -99; //服务器端未知异常

    const ROUTE_FAIL = -100;
    const UNKNOWN = 99999;

    protected static $PROPERTIES = [
        'message' => [
            self::SERVER_SUCCESS => '服务器端处理成功',
            self::SERVER_DECODE_ERR => '服务器端解码异常',
            self::SERVER_ENCODE_ERR => '服务器端编码异常',
            self::SERVER_NO_FUNC_ERR => '服务器端没有该函数',
            self::SERVER_NO_SERVANT_ERR => '服务器端无该Servant对象',
            self::SERVER_RESET_GRID => '服务器端灰度状态不一致',
            self::SERVER_QUEUE_TIMEOUT => '服务器队列超过限制',
            self::SERVER_ASYNC_CALL_TIMEOUT => '异步调用超时',
            self::SERVER_PROXY_CONNECT_ERR => 'proxy链接异常',
            self::SERVER_UNKNOWN_ERR => '服务器端未知异常',

            self::ROUTE_FAIL => '路由失败，请检查环境是否匹配，agent是否配置正确',
            self::TARS_PUT_BOOL_FAILED => 'bool类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_STRUCT_FAILED => 'struct类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_VECTOR_FAILED => 'vector类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_INT64_FAILED => 'int64类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_INT32_FAILED => 'int32类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_STRING_FAILED => 'sting类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_MAP_FAILED => 'map类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_FLOAT_FAILED => 'float类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_CHAR_FAILED => 'char类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_UINT8_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_SHORT_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_UINT16_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_UINT32_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',
            self::TARS_PUT_DOUBLE_FAILED => 'uint8类型打包失败，请检查是否传入了正确值',

            self::TARS_ENCODE_FAILED => 'taf编码失败，请检查数据类型，传入字段长度',
            self::TARS_DECODE_FAILED => 'taf解码失败，请检查传入的数据类型，是否从服务端接收到了正确的结果',

            self::TARS_GET_BOOL_FAILED => 'bool类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_STRUCT_FAILED => 'struct类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_VECTOR_FAILED => 'vector类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_INT64_FAILED => 'int64类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_INT32_FAILED => 'int32类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_STRING_FAILED => 'sting类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_MAP_FAILED => 'map类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_FLOAT_FAILED => 'float类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_CHAR_FAILED => 'char类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_UINT8_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_SHORT_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_UINT16_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_UINT32_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',
            self::TARS_GET_DOUBLE_FAILED => 'uint8类型解包失败，请检查是否传入了正确值',

            self::TARS_SOCKET_SET_NONBLOCK_FAILED => 'socket设置非阻塞失败',
            self::TARS_SOCKET_SEND_FAILED => 'socket发送失败',
            self::TARS_SOCKET_RECEIVE_FAILED => 'socket接收失败',
            self::TARS_SOCKET_SELECT_TIMEOUT => 'socket的select超时，也可以认为是svr超时',
            self::TARS_SOCKET_TIMEOUT => 'socket超时，一般是svr后台没回包，或者seq错误',
            self::TARS_SOCKET_CONNECT_FAILED => 'socket tcp 连接失败',
            self::TARS_SOCKET_CLOSED => 'socket tcp 服务端连接关闭',
            self::TARS_SOCKET_CREATE_FAILED => 'socket 创建失败',
            self::UNKNOWN => '未定义异常',
        ],
    ];
}
