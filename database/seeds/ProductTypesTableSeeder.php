<?php

use Illuminate\Database\Seeder;

class ProductTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {	//属性填充
        DB::table('attributes')->insert([[
            'name' => '颜色',
            'english_name'=> 'color'
             ],[
            'name'=>'尺寸',
            'english_name'=>'size']]);
        //产品种类填充
        DB::table('product_types')->insert([
            ['name'=>'服饰鞋包'],
            ['name'=>'日用百货'],
            ['name'=>'护肤彩妆'],
            ['name'=>'数码家电'],
            ['name'=>'运动户外'],
            ['name'=>'钟表首饰'],
            ['name'=>'母婴儿童'],
            ['name'=>'家具家装'],  
            ['name'=>'零售百货']]);
    }
}
