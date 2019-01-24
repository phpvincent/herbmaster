<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = ['name', 'title','is_open', 'domain_id','lock_prompt','secret','shield_area','shield_ip','theme','email','order_prefix','currency_id','fb_pix','google_pix','time_difference','payment_ids','fill_checkout_info','abandoned_checkout_email_time_delay'];
}
