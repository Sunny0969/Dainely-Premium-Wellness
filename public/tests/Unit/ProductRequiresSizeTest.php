<?php



namespace Tests\Unit;



use App\Support\ProductRequiresSize;

use Tests\TestCase;



class ProductRequiresSizeTest extends TestCase

{

    public function test_belt_requires_size_selection(): void

    {

        $this->assertTrue(ProductRequiresSize::check('dainely-belt', 'Dainely Belt'));

        $this->assertTrue(ProductRequiresSize::missingSelection('dainely-belt', 'Dainely Belt', null, null));

        $this->assertFalse(ProductRequiresSize::missingSelection('dainely-belt', 'Dainely Belt', 'L/XL', 'L/XL'));

    }



    public function test_french_and_german_belt_titles_require_size(): void

    {

        $this->assertTrue(ProductRequiresSize::check('8234567890', 'Ceinture Dainely'));

        $this->assertTrue(ProductRequiresSize::missingSelection('8234567890', 'Ceinture Dainely', null, null));

        $this->assertTrue(ProductRequiresSize::check('8234567890', 'Dainely Gürtel'));

        $this->assertTrue(ProductRequiresSize::missingSelection('8234567890', 'Dainely Gürtel', null, null));

    }



    public function test_belt_handle_is_recognized_when_product_id_is_numeric(): void

    {

        $this->assertTrue(ProductRequiresSize::check('8234567890', 'Product', 'dainely-comfort-belt'));

        $this->assertTrue(ProductRequiresSize::missingSelection('8234567890', 'Product', null, null, 'dainely-comfort-belt'));

    }



    public function test_non_belt_products_do_not_require_size(): void

    {

        $this->assertFalse(ProductRequiresSize::check('neck-pain', 'Neck Cloud'));

        $this->assertFalse(ProductRequiresSize::missingSelection('neck-pain', 'Neck Cloud', null, null));

    }

}


