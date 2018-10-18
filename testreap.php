<?php
$link1 = new \mysqli('127.0.0.1','root','root','',33060);
$link1->query("SELECT SLEEP(3) as wait, 'test' as test", MYSQLI_ASYNC);
$link2 = new \mysqli('127.0.0.1','root','root','',33060);
$link2->query("SELECT SLEEP(6) as wait, 'test' as test", MYSQLI_ASYNC);
$all_links = [$link1, $link2];
$toProcess = count($all_links);
$processed = 0;
do {
	$links = $errors = $reject = $all_links;
	if (!mysqli_poll($links, $errors, $reject, 0, 0)) {
		continue;
	}
	print "moving on";
	foreach ($links as $link) {
		if ($result = $link->reap_async_query()) {
			if (is_object($result)) {
				print_r($result->fetch_row());
				mysqli_free_result($result);
			}
			$processed++;
		}
	}
} while ($processed<$toProcess);