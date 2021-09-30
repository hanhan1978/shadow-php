<?php
/**
 * シングルプロセス、ブロッキングIO多重化のechoサーバー
 */

//TCP Socket を作成
$master_socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
//TIME_WAIT時にアドレス・ポートの使い回しが出来るようにセット
socket_set_option($master_socket, SOL_SOCKET, SO_REUSEADDR, 1);

//Socket に SRC Address, Port を bind して listen
if (!socket_bind($master_socket, '127.0.0.1', 8080) || !socket_listen($master_socket, 0)) {
    die("port in use\n");
}

//全てのソケットを保持する配列
$sockets = [$master_socket];

while (true) {
    //select用にreadソケット配列を作成
    $read = $sockets;

    $null = null; //write, except は今回は使わないのでnull、ここで完全にblockするのでsecondsもnull
    socket_select($read, $null, $null, $null);

    // 変更が検知されたsocketに対してそれぞれ処理を行う。
    foreach ($read as $socket) {
        //master_socketへの要求の場合は通信用の子ソケットを作る
        if($socket === $master_socket){
            echo "connected\n";
            $sockets[] = socket_accept($master_socket);
        }else{
            // 子ソケットの場合は、送信されてきた通信内容を読んで処理を行う。
            $input = socket_read($socket, 1024);
            if (trim($input) === 'quit') {
                $key = array_search($socket, $sockets);
                unset($sockets[$key]);
                socket_close($socket);
            } else {
                // 相手が入力してきた文字列をそのまま返す
                socket_write($socket, $input);
            }
        }
    }
}