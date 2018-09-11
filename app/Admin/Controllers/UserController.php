<?php

namespace App\Admin\Controllers;

use App\Admin\Models\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Request;

class UserController extends Controller
{

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('用户管理');

            $content->description('');

            $content->body($this->grid());
        });
    }

    public function show($id, Content $content)
    {
        return $content
            ->header('用户信息')
            ->description('用户详情')
            ->body(Admin::show(User::findOrFail($id), function (Show $show) {
                $show->avatarUrl()->image('', 150, 150);
            }));
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑用户信息');

            $content->description('');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('创建用户');

            $content->description('');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(User::class, function (Grid $grid) {

            $grid->paginate(20);

            $grid->avatarUrl('头像')->image('', 60, 60);

            $grid->id('用户ID')->sortable();

            $grid->openId('openID');

            $grid->cId('渠道ID');

            $grid->nickName('昵称');

            $grid->gender('性别')->using(['0' => '未知', '1' => '男', '2' => '女']);

            $grid->language('语言');

//            $grid->country('国家');
//
//            $grid->province('省份');
//
//            $grid->city('城市');

            $grid->created_at('创建时间');

//            $grid->updated_at('更新时间');

            $grid->filter(function (Grid\Filter $filter) {

                $filter->scope('Male', '男')->where('gender', '=', 1);

                $filter->scope('Female', '女')->where('gender', '=', 2);

            });

//            $grid->disableActions();

            $grid->disableCreateButton();

            $grid->disableRowSelector();

            $grid->actions(function (Grid\Displayers\Actions $actions) {

                $actions->disableDelete();

                $actions->disableEdit();

//                $actions->disableView();

            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(User::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }
}
