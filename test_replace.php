<?php
$html = '<!-- Right Column: Responsive YouTube Iframe -->
<div style="position: relative; width: 100%; padding-top: 56.25%; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 24px rgba(0,0,0,0.12); background-color: #000000;"><iframe style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" title="Pickleball Balance and Injury Prevention" src="https://www.youtube.com/embed/sNCBUGDUgfk" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen="allowfullscreen">
        </iframe></div>';

$replaced = str_replace('<iframe ', '<iframe referrerpolicy="strict-origin-when-cross-origin" ', $html);
echo $replaced;
