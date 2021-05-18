<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Customer;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CustomerController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Customer(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name', '企业名称');
            $grid->column('api_id');
            $grid->column('api_key');
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Customer(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('api_id');
            $show->field('api_key');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Customer(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('api_id');
            $form->text('api_key');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    public function list() {
        $res = \App\Models\Customer::query()->select("id", "name")->get()->toArray();
        return $res;
    }
}
