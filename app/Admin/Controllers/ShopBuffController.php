<?php

namespace App\Admin\Controllers;

use App\Admin\Models\ShopBuff;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;

class ShopBuffController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('商店管理')
            ->description('')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {

        return $content
            ->header('编辑商品')
            ->description('')
            ->body($this->form()->edit($id));

    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ShopBuff::class, function (Grid $grid) {

            $grid->paginate(20);

            $grid->id('商品ID')->sortable();

            $grid->name('名称');

            $grid->costVal('花费值');

            $grid->costType('花费类型')->display(function ($costType){
                return $costType == 1 ? '钻石' : '金币';
            });

            $grid->buffType('增益效果')->display(function ($buffType){
                return $buffType == 1 ? '加速' : '离线';
            });

            $grid->timeSec('持续时间（sec）');

            $grid->desc('描述');

            $grid->filter(function (Grid\Filter $filter) {

            });

            $grid->disableCreateButton();

            $grid->disableRowSelector();

            $grid->actions(function (Grid\Displayers\Actions $actions) {

                //$actions->disableDelete();

                //$actions->disableEdit();

                $actions->disableView();

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ShopBuff::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->costVal('CostVal');
        $show->costType('CostType');
        $show->buffType('BuffType');
        $show->timeSec('TimeSec');
        $show->desc('Desc');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ShopBuff);

        $form->text('name', '名称');

        $form->number('costVal', '花费值');

        $form->radio('costType', '消费类型')->options([
            1 => '钻石',
            2 => '金币',
        ])->stacked();

        $form->radio('buffType', '增益效果')->options([
            1 => '加速',
            2 => '离线',
        ])->stacked();

        $form->number('timeSec', 'TimeSec');

        $form->text('desc', 'Desc');

        $form->tools(function(Form\Tools $tools) {
            $tools->disableView();
        });


        return $form;
    }
}
