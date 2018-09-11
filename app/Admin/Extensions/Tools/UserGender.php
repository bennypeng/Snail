<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;
use Illuminate\Support\Facades\Request;

class UserGender extends AbstractTool
{
    public function script()
    {
        $url = Request::fullUrlWithQuery(['gender' => '_gender_']);

        return <<<EOT

$('input:radio.user-gender').change(function () {

    var url = "$url".replace('_gender_', $(this).val());

    $.pjax({container:'#pjax-container', url: url });

});

EOT;
    }

    public function render()
    {
        Admin::script($this->script());

        $options = [
            '-1'    => '全部',
            '0'     => '未知',
            '1'     => '男',
            '2'     => '女',
        ];

        return view('admin.tools.gender', compact('options'));
    }
}
