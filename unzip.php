<?php
$zip = new ZipArchive;
// Check karein agar zip file khul sakti hai
if ($zip->open('vendor.zip') === TRUE) {
    // Isay isi folder me extract (unzip) karein
    $zip->extractTo(__DIR__);
    $zip->close();
    echo '<h3>🎉 Vendor file successfully unzip ho gayi hai!</h3>';
} else {
    echo '<h3>❌ Failed: vendor.zip file nahi khul saki.</h3>';
}
?>