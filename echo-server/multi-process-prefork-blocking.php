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
foreach(range(0,1) as $i){ //２つの子プロセスを作成する
    $pid = pcntl_fork();
    if($pid === 0){ //子プロセスの場合
        while(true) {
            $client_sock = socket_accept($sock);
            $buf = socket_read($client_sock, 1024);
            socket_write($client_sock, $buf);
            socket_close($client_sock);
        }
    }else{
        //親プロセスの場合は、pidを収集
        $pids[] = $pid;
    }
}

foreach($pids as $pid){
    //親側のプロセスは、子プロセスの状態を監視する（ここで処理はBlockする）
    pcntl_waitpid($pid, $status);
}
