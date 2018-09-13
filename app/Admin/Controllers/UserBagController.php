<?php

namespace App\Admin\Controllers;

use App\Admin\Models\UserBag;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;

class UserBagController extends Controller
{

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('背包管理')
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
        return Admin::grid(UserBag::class, function (Grid $grid) {

            $grid->paginate(20);

            $grid->userId('用户ID')->sortable();

            $grid->gold('金币');

            $grid->diamond('钻石');

            $grid->column('SNAIL_MAP')->display(function () {
                return [
                    [$this->item_1, $this->item_2, $this->item_3],
                    [$this->item_4, $this->item_5, $this->item_6],
                    [$this->item_7, $this->item_8, $this->item_9],
                    [$this->item_10, $this->item_11, $this->item_12],
                    [$this->item_13, $this->item_14, $this->item_15]
                    ];
            })->snailMapTable();

            $grid->filter(function (Grid\Filter $filter) {


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
        $show = new Show(UserBag::findOrFail($id));

        $show->id('Id');
        $show->userId('UserId');
        $show->gold('Gold');
        $show->diamond('Diamond');
        $show->item_1('Item 1');
        $show->item_2('Item 2');
        $show->item_3('Item 3');
        $show->item_4('Item 4');
        $show->item_5('Item 5');
        $show->item_6('Item 6');
        $show->item_7('Item 7');
        $show->item_8('Item 8');
        $show->item_9('Item 9');
        $show->item_10('Item 10');
        $show->item_11('Item 11');
        $show->item_12('Item 12');
        $show->item_13('Item 13');
        $show->item_14('Item 14');
        $show->item_15('Item 15');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new UserBag);

        $form->number('userId', 'UserId');
        $form->text('gold', 'Gold');
        $form->number('diamond', 'Diamond');
        $form->text('item_1', 'Item 1')->default('[]');
        $form->text('item_2', 'Item 2')->default('[]');
        $form->text('item_3', 'Item 3')->default('[]');
        $form->text('item_4', 'Item 4')->default('[]');
        $form->text('item_5', 'Item 5')->default('[]');
        $form->text('item_6', 'Item 6')->default('[]');
        $form->text('item_7', 'Item 7')->default('[]');
        $form->text('item_8', 'Item 8')->default('[]');
        $form->text('item_9', 'Item 9')->default('[]');
        $form->text('item_10', 'Item 10')->default('[]');
        $form->text('item_11', 'Item 11')->default('[]');
        $form->text('item_12', 'Item 12')->default('[]');
        $form->text('item_13', 'Item 13')->default('[]');
        $form->text('item_14', 'Item 14')->default('[]');
        $form->text('item_15', 'Item 15')->default('[]');

        return $form;
    }
}
