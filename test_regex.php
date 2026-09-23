<?php
$str = "https://www.youtube.com/watch?v=XqZsoesa55w";
if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([\w\-]+)~i', $str, $m)) {
    echo $m[1];
}