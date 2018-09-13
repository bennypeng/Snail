<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Config;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Show;

class ConfigController extends Controller
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
            ->header('配置管理')
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
            ->header('编辑配置')
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
        return Admin::grid(Config::class, function (Grid $grid) {

            $grid->paginate(20);

            $grid->id('配置ID')->sortable();

            $grid->key('配置项');

            $grid->value('值');

            $grid->desc('描述');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('key', '配置项');
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
        $show = new Show(Config::findOrFail($id));

        $show->id('Id');
        $show->key('Key');
        $show->value('Value');
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
        $form = new Form(new Config);

        $form->text('key', '配置项')->readOnly();

        $form->text('value', '值');

        $form->text('desc', '描述');

        $form->tools(function(Form\Tools $tools) {
            $tools->disableView();
        });

        return $form;
    }
}
