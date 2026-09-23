<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$shopify = app(\App\Services\ShopifyService::class);
$query = <<<'GRAPHQL'
{
  products(first: 10, query: "title:*Dainely Belt 2.0*") {
    edges {
      node {
        id
        title
        handle
        variants(first: 5) {
          edges {
            node {
              id
              title
              price
            }
          }
        }
      }
    }
  }
}
GRAPHQL;

$res = $shopify->graphql($query);
echo json_encode($res, JSON_PRETTY_PRINT);