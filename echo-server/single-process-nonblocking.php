<?php
/**
 * シングルプロセス、NonブロッキングIOのechoサーバー
 */

//TCP Socket を作成
$sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
//TIME_WAIT時にアドレス・ポートの使い回しが出来るようにセット
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
socket_set_nonblock($sock);

//Socket に SRC Address, Port を bind して listen
if (!socket_bind($sock, '127.0.0.1', 8080) || !socket_listen($sock, 0)) {
    die("port in use\n");
}

$sockets = [$sock];

while (true) {
    //client からの接続を待つ (block)

    $unset_socket_keys = [];
    foreach($sockets as $i => $socket){

        if($socket === $sock){
            if($client_sock = socket_accept($sock)) {
                echo "connected\n";
                socket_set_nonblock($client_sock);
                $sockets[] = $client_sock;
            }
        }else{
            $buf = socket_read($socket, 1024);
            if($buf && strlen($buf) > 0){
                socket_write($socket, $buf);
                socket_close($socket);
                $unset_socket_keys[] = $i;
            }
        }
    }

    foreach($unset_socket_keys as $key){
        unset($sockets[$key]);
    }
}
