<?php

namespace App\Admin\Controllers;

use App\Admin\Models\DailyReward;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;

class DailyRewardController extends Controller
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
            ->header('登录奖励管理')
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
            ->header('奖励编辑')
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
//        $grid = new Grid(new DailyReward);
//
//        $grid->id('Id');
//        $grid->day('Day');
//        $grid->diamond('Diamond');
//        $grid->gold('Gold');
//        $grid->snailIds('SnailIds');
//
//        return $grid;
        return Admin::grid(DailyReward::class, function (Grid $grid) {

            $grid->paginate(20);

            $grid->id('ID')->sortable();

            $grid->day('天');

            $grid->diamond('钻石');

            $grid->gold('金币');

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
        $show = new Show(DailyReward::findOrFail($id));

        $show->id('Id');
        $show->day('Day');
        $show->diamond('Diamond');
        $show->gold('Gold');
        $show->snailIds('SnailIds');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DailyReward);

        $form->number('day', '天')->default(1);

        $form->number('diamond', '钻石')->default(0);

        $form->number('gold', '金币')->default(0);
        //$form->text('snailIds', 'SnailIds');

        $form->tools(function(Form\Tools $tools) {
            $tools->disableView();
        });

        return $form;
    }
}
