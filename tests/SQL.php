<?php

use Pina\Config;
use Pina\DB;
use Pina\SQL;

class SQLTest extends PHPUnit_Framework_TestCase
{
    
    public function testByCondition()
    {
        Config::initPath(__DIR__.'/config');
        
        $q = SQL::table('cody_product')->makeByCondition(['=', SQL::SQL_OPERAND_FIELD, 'product_id', SQL::SQL_OPERAND_VALUE, 5]);
        $this->assertEquals("cody_product.product_id = '5'", $q);
        
        $q = SQL::table('cody_product')->makeByCondition(['=', SQL::SQL_OPERAND_FIELD, 'product_id', SQL::SQL_OPERAND_FIELD, 'product_id'], 'cody_product_variand');
        $this->assertEquals("cody_product.product_id = cody_product_variand.product_id", $q);
        
        $q = SQL::table('cody_product')->makeByCondition(['=', SQL::SQL_OPERAND_FIELD, 'product_id', SQL::SQL_OPERAND_FIELD, 'cody_product_feature.product_id'], 'cody_product_variand');
        $this->assertEquals("cody_product.product_id = cody_product_feature.product_id", $q);
        
        #exception unsupported format
        #$q = SQL::table('cody_product')->makeByCondition(['=', SQL::SQL_OPERAND_VALUE, array(1,2,3), SQL::SQL_OPERAND_FIELD, 'cody_product_feature.product_id'], 'cody_product_variand');
        #echo $q;
        
        
    }
    
    public function testWhere()
    {
        Config::initPath(__DIR__.'/config');
        
        $q = SQL::table('cody_product')->whereBy('product_id', 5)->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id = '5')", $q);
        
        $q = SQL::table('cody_product')->whereBy('product_id', array(1,2,3))->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id IN ('1','2','3'))", $q);
        
        $q = SQL::table('cody_product')->whereNotBy('product_id', 5)->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id <> '5')", $q);
        
        $q = SQL::table('cody_product')->whereNotBy('product_id', array(1,2,3))->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id NOT IN ('1','2','3'))", $q);
        
        $q = SQL::table('cody_product')->whereLike('product_title', '123')->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_title LIKE '123')", $q);
        
        $q = SQL::table('cody_product')->whereNotLike('product_title', '123')->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_title NOT LIKE '123')", $q);
        
        $q = SQL::table('cody_product')->whereBetween('product_id', 1, 5)->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id BETWEEN '1' AND '5')", $q);
        
        $q = SQL::table('cody_product')->whereNotBetween('product_id', 1, 5)->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id NOT BETWEEN '1' AND '5')", $q);
        
        $q = SQL::table('cody_product')->whereBy(array('product_id', 'brand_id'), array(1,2,3))->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id IN ('1','2','3') OR cody_product.brand_id IN ('1','2','3'))", $q);
        
        $q = SQL::table('cody_product')->whereLike('product_title', array(1,2,3))->make();
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_title LIKE '1' OR cody_product.product_title LIKE '2' OR cody_product.product_title LIKE '3')", $q);
        
        $q = SQL::table('cody_product')
            ->whereBy(array('product_id', 'brand_id'), array(1,2,3))
            ->whereLike(array('product_title', 'brand_title'), 'kuku')
            ->whereLike(array('product_title', 'brand_title'), array(1, 2 ,3))
            ->make();
        
        $this->assertEquals("SELECT * FROM cody_product WHERE (cody_product.product_id IN ('1','2','3') OR cody_product.brand_id IN ('1','2','3'))"
            . " AND (cody_product.product_title LIKE 'kuku' OR cody_product.brand_title LIKE 'kuku')"
            . " AND ("
                . "cody_product.product_title LIKE '1'"
                . " OR cody_product.product_title LIKE '2'"
                . " OR cody_product.product_title LIKE '3'"
                . " OR cody_product.brand_title LIKE '1'"
                . " OR cody_product.brand_title LIKE '2'"
                . " OR cody_product.brand_title LIKE '3'"
            . ")", $q);
    }

    public function testJoin()
    {
        Config::initPath(__DIR__.'/config');
        $q = SQL::table('cody_product')->leftJoin('cody_product_variant', 'product_id', 'cody_product', 'product_id')->make();
        $this->assertEquals("SELECT * FROM cody_product LEFT JOIN cody_product_variant ON cody_product_variant.product_id = cody_product.product_id", $q);
        
        $q = SQL::table('cody_pick_list_order')->leftJoin('cody_pick_list', array(
                'pick_list_id' => array('cody_pick_list_order' => 'pick_list_id'),
                'pick_list_type' => 'buyer'
        ))->make();
        $this->assertEquals("SELECT * FROM cody_pick_list_order LEFT JOIN cody_pick_list ON cody_pick_list.pick_list_id = cody_pick_list_order.pick_list_id AND cody_pick_list.pick_list_type ='buyer'", $q);
        
        $q = SQL::table('cody_product')->leftJoin(
            SQL::table('cody_product_variant')->on('product_id')
        )->make();
        $this->assertEquals("SELECT * FROM cody_product LEFT JOIN cody_product_variant ON cody_product_variant.product_id = cody_product.product_id", $q);

        $q = SQL::table('cody_pick_list_order')->leftJoin(
            SQL::table('cody_pick_list')
                ->on('pick_list_id')
                ->onBy('pick_list_type', 'buyer')
        )->make();
        $this->assertEquals("SELECT * FROM cody_pick_list_order LEFT JOIN cody_pick_list ON cody_pick_list.pick_list_id = cody_pick_list_order.pick_list_id AND cody_pick_list.pick_list_type = 'buyer'", $q);

        $q = SQL::table('cody_pick_list_order')
            ->alias('plo')
            ->leftJoin(
                SQL::table('cody_pick_list')
                    ->alias('pl')
                    ->whereBy('pick_list_id', 5)
                    ->on('pick_list_id')
                    ->onBy('pick_list_type', 'buyer')
            )
            ->make();
        $this->assertEquals("SELECT * FROM cody_pick_list_order plo LEFT JOIN cody_pick_list pl ON pl.pick_list_id = plo.pick_list_id AND pl.pick_list_type = 'buyer' WHERE (pl.pick_list_id = '5')", $q);

        
        $q = SQL::table('cody_category')
            ->select("category_id, category_title")
            ->leftJoin(
                SQL::table('cody_category_parent')->alias('t2')->on('category_id')->select('category_parent_id')
                    ->leftJoin(
                        SQL::table('cody_category')->alias('t3')->on('category_id', 'category_parent_id')->select('category_title AS category_parent_title')
                    )
            )
            ->orderBy('t2.category_id, t2.category_parent_length DESC')
            ->make();
        
        $this->assertEquals("SELECT category_id, category_title, category_parent_id, category_title AS category_parent_title"
            . " FROM cody_category"
            . " LEFT JOIN cody_category_parent t2 ON t2.category_id = cody_category.category_id"
            . " LEFT JOIN cody_category t3 ON t3.category_id = t2.category_parent_id"
            . " ORDER BY t2.category_id, t2.category_parent_length DESC", $q);
        
        
        $pickListProductIds = array(1,2,3);
        $q = SQL::table('cody_product_variant')
            ->innerJoin(
                SQL::subquery(
                    SQL::table('cody_pick_list_product')
                        ->whereBy('pick_list_product_id', $pickListProductIds)
                        ->whereNotBy('pick_list_product_amount_status', 'decreased')
                        ->groupBy('product_variant_id')
                )
                ->alias('plp')
                ->on('product_variant_id')

            )
            ->innerJoin(
                SQL::table('cody_pick_list_product')
                    ->select('cody_pick_list_product.pick_list_product_id')
                    ->on('product_variant_id')
                    ->onBy('pick_list_product_id', $pickListProductIds)
                    ->onNotBy('pick_list_product_amount_status', 'decreased')
                    ->leftJoin(
                        SQL::table('cody_order_product')->on('order_product_id')
                    )
            )
            ->make();
        
        $this->assertEquals("SELECT cody_pick_list_product.pick_list_product_id FROM cody_product_variant"
            . " INNER JOIN ("
                . "SELECT * FROM cody_pick_list_product"
                . " WHERE (cody_pick_list_product.pick_list_product_id IN ('1','2','3'))"
                . " AND (cody_pick_list_product.pick_list_product_amount_status <> 'decreased')"
                . " GROUP BY product_variant_id"
                . ") plp ON plp.product_variant_id = cody_product_variant.product_variant_id"
            . " INNER JOIN cody_pick_list_product ON cody_pick_list_product.product_variant_id = cody_product_variant.product_variant_id"
                . " AND cody_pick_list_product.pick_list_product_id IN ('1','2','3')"
                . " AND cody_pick_list_product.pick_list_product_amount_status <> 'decreased'"
            . " LEFT JOIN cody_order_product ON cody_order_product.order_product_id = cody_pick_list_product.order_product_id", $q);
        
        
        

        /*
        $pickListProductIds = array(1,2,3);
        SQL::table('cody_product_variant')
            ->innerJoin(
                SQL::subquery(
                    SQL::table('cody_pick_list_product')
                        ->whereBy('pick_list_product_id', $pickListProductIds)
                        ->whereNotBy('pick_list_product_amount_status', 'decreased')
                        ->groupBy('product_variant_id')
                )
                ->alias('plp')
                ->on('product_variant_id')

            )
            ->innerJoin(
                SQL::table('cody_pick_list_product')
                    ->on('product_variant_id')
                    ->onBy('pick_list_product_id', $pickListProductIds)
                    ->onNotBy('pick_list_product_amount_status', 'decreased')
                    ->leftJoin(
                        SQL::table('cody_order_product')->on('order_product_id')
                    )
            )
            ->updateOperation("cody_product_variant.".$amountField." = cody_product_variant.".$amountField."
                    - IF(cody_product_variant.product_variant_stock = 'backorder', 0, plp.pick_list_product_amount),
                cody_pick_list_product.pick_list_product_amount_status = 'decreased',
                cody_order_product.$statusField = 'none'");
        
        $q = "UPDATE cody_product_variant 
                INNER JOIN (
                    SELECT product_variant_id, SUM(pick_list_product_amount) as pick_list_product_amount 
                    FROM cody_pick_list_product 
                    WHERE cody_pick_list_product.pick_list_product_id IN ('".join("','", $pickListProductIds)."')
                    AND cody_pick_list_product.pick_list_product_amount_status <> 'decreased'
                    GROUP BY cody_pick_list_product.product_variant_id
                ) as plp ON cody_product_variant.product_variant_id = plp.product_variant_id
                INNER JOIN cody_pick_list_product ON 
                    cody_pick_list_product.product_variant_id = cody_product_variant.product_variant_id
                    AND cody_pick_list_product.pick_list_product_id IN ('".join("','", $pickListProductIds)."')
                    AND cody_pick_list_product.pick_list_product_amount_status <> 'decreased'
                LEFT JOIN cody_order_product ON
                    cody_order_product.order_product_id = cody_pick_list_product.order_product_id
            SET
                cody_product_variant.".$amountField." = cody_product_variant.".$amountField."
                    - IF(cody_product_variant.product_variant_stock = 'backorder', 0, plp.pick_list_product_amount),
                cody_pick_list_product.pick_list_product_amount_status = 'decreased',
                cody_order_product.$statusField = 'none'";
        */
    }
    
    public function testSelect()
    {
        $gw = SQL::table('cody_product');

        $useStock = true;
        $gw->innerJoin('cody_brand', 'brand_id', 'cody_product', 'brand_id')
            ->select('cody_brand.brand_title')

            ->select('cody_product.product_id, cody_product.product_sku')
            ->select('cody_product.product_title, cody_product.product_color')

            ->innerJoin('cody_product_variant', 'product_id', 'cody_product', 'product_id')
            ->select('cody_product_variant.product_variant_size')
            ->select('SUM(cody_product_variant.product_variant_amount) as product_variant_amount')

            ->leftJoin('cody_product_reserv', 'product_variant_id', 'cody_product_variant', 'product_variant_id')
            ->select('SUM(cody_product_reserv.product_reserv_amount) as product_reserv_amount')

            ->leftJoin('cody_order_product', array(
                'product_variant_id' => array('cody_product_variant' => 'product_variant_id'),
                'order_product_amount_status' => 'reserved',
                //'order_id' => array('cody_order' => 'order_id'),
            ))
            ->select($useStock?'SUM(cody_order_product.order_product_amount) + SUM(cody_order_product.order_product_stock_amount) as order_product_amount':'SUM(cody_order_product.order_product_amount) as order_product_amount')
            //->whereBy('cody_order.order_buyer_status', array('confirmed'))
            //->whereBy('cody_order.order_provider_status', array('new'))

            ->select('SUM(cody_product_variant.product_variant_amount '
                .($useStock?(' + cody_product_variant.product_variant_stock_amount - cody_product_variant.product_variant_stock_reserv - IFNULL(cody_order_product.order_product_stock_amount,0)'):'')
                . ' - IFNULL(cody_product_reserv.product_reserv_amount,0)'
                . ' - IFNULL(cody_order_product.order_product_amount,0)'
                . ') as stock')

            ->groupBy('cody_product_variant.product_variant_id')
            ->orderBy('brand_title asc, product_sku asc');
        
        $this->assertTrue(strpos($gw->make(), ' - IFNULL(cody_product_reserv.product_reserv_amount,0)'
                . ' - IFNULL(cody_order_product.order_product_amount,0)'
                . ') as stock') !== false);
    }

}
