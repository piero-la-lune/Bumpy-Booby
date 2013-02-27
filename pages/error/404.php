<?php

header('HTTP/1.1 404 Not Found');

$title = Trad::W_NOTFOUND;

$content = '<h1>404 – '.Trad::W_NOTFOUND.'</h1>'
	.'<p>'.Trad::S_NOTFOUND.'</p>';

?>