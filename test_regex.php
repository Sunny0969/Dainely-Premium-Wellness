<?php
$content = "<p><img src=\"https://media.dainely.com/dainely-media/optimized/editor/1789759755-5lhKoDoFCr.jpg\"></p>";
$content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759755-[^\.]+\.jpg/", "/images/women-walking.jpg", $content);
echo $content;

