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
    $client_sock = socket_accept($sock);
    $pid = pcntl_fork();
    if($pid === 0){
        $child_sock = $client_sock;
        echo "connected\n";
        //client からの文字入力を待つ (block)
        $buf = socket_read($child_sock, 1024);
        socket_write($child_sock, $buf);
        socket_close($child_sock);
        socket_close($sock);
        echo "close connection\n";
        exit(0);
    }else{
        $client_sock =null;
    }
}





