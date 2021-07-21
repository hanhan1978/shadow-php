<?php
//TCP Socket を作成
$sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
//TIME_WAIT時にアドレス・ポートの使い回しが出来るようにセット
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

//Socket に SRC Address, Port を bind して listen
if(!socket_bind($sock, '127.0.0.1', 8080) || !socket_listen($sock, 0)){
    die("port in use\n");
}
while(true){
    $client_sock = socket_accept($sock); //親プロセスはここでblockして接続を待つ
    $pid = pcntl_fork();
    if($pid === 0){ //子プロセス
        echo "connected\n";
        //client からの文字入力を待つ (block)
        $buf = socket_read($client_sock, 1024);
        socket_write($client_sock, $buf);
        socket_close($client_sock);
        socket_close($sock);
        echo "close connection\n";
        exit(0);
    }else{ //親プロセス
        //参照が残っていると、子側のsocket_closeが効かなくなることへの対処
        unset($client_sock);
    }
}
