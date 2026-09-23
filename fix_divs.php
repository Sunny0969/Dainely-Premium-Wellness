<?php
$file = "d:/dainly Project/Dainely-Premium-Wellness/resources/views/partials/product-landing-premium.blade.php";
$content = file_get_contents($file);

$search = <<<EOT
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </button>
    </div>
  </div>
</div>

</div>{{-- /x-data productPurchase --}}
EOT;

$replace = <<<EOT
        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </button>
    </div>
    </div>
  </div>
</div>

</div>{{-- /x-data productPurchase --}}
EOT;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Done";