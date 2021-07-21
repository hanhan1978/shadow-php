<?php
//TCP Socket を作成
$sock = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
//TIME_WAIT時にアドレス・ポートの使い回しが出来るようにセット
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

//Socket に SRC Address, Port を bind して listen
if(!socket_bind($sock, '127.0.0.1', 8080) || !socket_listen($sock, 0)){
    die("port in use\n");
}

$pids = [];
foreach(range(1,2) as $i){
    $pid = pcntl_fork();
    if($pid !== 0){
        $pids[] = $pid;
    }else{
        while(true) {
            $client_sock = socket_accept($sock);
            $buf = socket_read($client_sock, 1024);
            socket_write($client_sock, $buf);
            socket_close($client_sock);
        }
    }
}

foreach($pids as $pid){
    pcntl_waitpid($pid, $status);
    echo("checking status[$status]\n");
}
