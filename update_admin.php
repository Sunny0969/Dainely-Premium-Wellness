<?php
$file = "resources/views/layouts/admin.blade.php";
$content = file_get_contents($file);
$content = str_replace(
    "<form action=\"/dainely-admin-panel/logout\" method=\"POST\">",
    "<a href=\"/dainely-admin-panel/profile\" class=\"flex/items-center px-4 py-2.5 rounded-lg font-medium text-slate-400 hover:text-white transition duration-150\">&#x9269; Admin Settings</a>\n                <form action=\"/dainely-admin-panel/logout\" method=\"POST\">",
    $content
NÂ™¥±•}ÁÕÑ}½¹Ñ•¹ÑÌ ‘™¥±”°€‘½¹Ñ•¹Ğ¤
