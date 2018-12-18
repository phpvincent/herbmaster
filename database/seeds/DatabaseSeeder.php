<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 新增超级管理员组
        DB::table('admin_group')->insert([
            'group_name' => '超级管理员',
            'group_rule'=> '0',
        ]);

        // 新增超级管理员
        DB::table('admins')->insert([
            'username' => 'root',
            'password'=> bcrypt('123456'),
            'show_name'        => '超级管理员',
            'is_root'      => '1',
            'num'      => '0',
            'admin_use'        => '1',
            'admin_group'      => '1',
            'admin_method'      => '1',
            'is_order'      => '1',
            'admin_rule'      => '3',
            'admin_languages'      => '0',
        ]);
    }
}
