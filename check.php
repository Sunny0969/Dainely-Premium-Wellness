<?php $c = file_get_contents("routes/web.php"); if (str_starts_with($c, "\xEF\xBB\xBF")) echo "BOM found"; else echo "Clean"; 
