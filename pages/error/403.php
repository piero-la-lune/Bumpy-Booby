<?php

header('HTTP/1.1 403 Forbidden');

$title = Trad::W_FORBIDDEN;

$content = '
	<h1>403 – '.Trad::W_FORBIDDEN.'</h1>
	<p>'.Trad::S_FORBIDDEN.'</p>
';

?>