--TEST--
Host name mismatch triggers error
--SKIPIF--
<?php
if (!extension_loaded("openssl")) die("skip");
if (!function_exists('pcntl_fork')) die("skip no fork");
--FILE--
<?php
$serverUri = "ssl://127.0.0.1:64321";
$serverFlags = STREAM_SERVER_BIND | STREAM_SERVER_LISTEN;
$serverCtx = stream_context_create(['ssl' => [
    'local_cert' => __DIR__ . '/bug54992.pem'
]]);
$server = stream_socket_server($serverUri, $errno, $errstr, $serverFlags, $serverCtx);

$pid = pcntl_fork();
if ($pid == -1) {
    die('could not fork');
} else if ($pid) {

    $clientFlags = STREAM_CLIENT_CONNECT;
    $clientCtx = stream_context_create(['ssl' => [
        'verify_peer' => true,
	'cafile' => __DIR__ . '/bug54992-ca.pem'
    ]]);

    $client = stream_socket_client($serverUri, $errno, $errstr, 1, $clientFlags, $clientCtx);
    var_dump($client);

} else {
    @pcntl_wait($status);
    @stream_socket_accept($server, 1);
}
--EXPECTF--
Warning: stream_socket_client(): Peer certificate CN=`bug54992.local' did not match expected CN=`127.0.0.1' in %s on line %d

Warning: stream_socket_client(): Failed to enable crypto in %s on line %d

Warning: stream_socket_client(): unable to connect to ssl://127.0.0.1:64321 (Unknown error) in %s on line %d
bool(false)
