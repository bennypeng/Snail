<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Snail;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;

class SnailController extends Controller
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
            ->header('蜗牛管理')
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
            ->header('Edit')
            ->description('description')
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
        return Admin::grid(Snail::class, function (Grid $grid) {

            $grid->paginate(20);

            $grid->id('蜗牛ID')->sortable();

            $grid->level('等级');

            $grid->goldUnlockId('金币解锁等级')->editable();

            $grid->diamondUnlockId('钻石解锁等级')->editable();

            $grid->goldBase('金币基数');

            $grid->goldFactor('金币因子');

            $grid->goldPower('金币幂值');

            $grid->costGoldFactor('金币花费因子');

            $grid->cycleEarnGold('收益（gold/sec）');

            $grid->cycleMSec('速度（sec）');

            $grid->diamondPrice('钻石价格');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('level', '等级');
            });

            $grid->disableCreateButton();

            $grid->disableRowSelector();

            $grid->disableActions();

//            $grid->actions(function (Grid\Displayers\Actions $actions) {
//
//                $actions->disableDelete();
//
//                $actions->disableEdit();
//
//                $actions->disableView();
//
//            });
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
        $show = new Show(Snail::findOrFail($id));

        $show->id('Id');
        $show->level('Level');
        $show->resIdx('ResIdx');
        $show->goldUnlockId('GoldUnlockId');
        $show->diamondUnlockId('DiamondUnlockId');
        $show->goldBase('GoldBase');
        $show->goldFactor('GoldFactor');
        $show->goldPower('GoldPower');
        $show->costGoldFactor('CostGoldFactor');
        $show->cycleEarnGold('CycleEarnGold');
        $show->cycleMSec('CycleMSec');
        $show->diamondPrice('DiamondPrice');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Snail);

        $form->number('level', 'Level')->default(1);
        $form->number('resIdx', 'ResIdx');
        $form->number('goldUnlockId', 'GoldUnlockId');
        $form->number('diamondUnlockId', 'DiamondUnlockId');
        $form->number('goldBase', 'GoldBase');
        $form->decimal('goldFactor', 'GoldFactor')->default(0.00);
        $form->number('goldPower', 'GoldPower');
        $form->decimal('costGoldFactor', 'CostGoldFactor')->default(0.00);
        $form->number('cycleEarnGold', 'CycleEarnGold');
        $form->number('cycleMSec', 'CycleMSec');
        $form->number('diamondPrice', 'DiamondPrice')->default(-1);

        return $form;
    }
}
