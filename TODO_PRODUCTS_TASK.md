# TODO: Products dropdown + products page search/filter + click-to-open

- [ ] Inspect current `resources/views/partials/header.blade.php` product dropdown link generation and ensure it points to `products.show` with correct slug.
- [ ] Inspect `app/Support/ProductSlugResolver.php` and ensure it resolves Shopify handles/slugs correctly.
- [ ] Ensure `resources/views/pages/products/index.blade.php` card links pass the correct slug expected by `ProductController@show`.
- [ ] Keep hover behavior only for opening dropdown; ensure product navigation happens only on click.
- [ ] Test flow:
  - hover Products dropdown -> shows Dainely belt/shop items
  - click -> opens correct product detail page
  - product detail page has access to full products list search/filter
  - search + filter works and shows only matching products

