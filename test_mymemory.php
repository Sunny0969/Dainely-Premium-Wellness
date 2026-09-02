<?php $res = file_get_contents("https://api.mymemory.translated.net/get?q=".urlencode("Move Better")."&langpair=en|fr&de=admin@dainelylab.com"); print_r($res);
