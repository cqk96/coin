<?php
/**
 * 模型描述
 *
 * @package AdAccountCompany
 * @author UserName
 * @version 0.1
 * @copyright (C) 2023
 * @license MIT
 */

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;

class GateList extends Authenticatable
{
    protected $connection = 'mysql';
    protected $table = 'gate_list';
    protected $guarded = [ 'id' ];
}
