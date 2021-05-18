<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use App\Models\Customer;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class UserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(['customer']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name', '账号');
            $grid->column('password', '密码')->display(function ($value) {
                try {
                    return $value;
                } catch (\Exception $exception) {
                    Log::info($exception->getMessage());
                    return '密码格式错误';
                }
            });
            $grid->column('customer.name', '企业名称');
            $grid->column('created_at', '创建时间');
            $grid->column('updated_at', '更新时间')->sortable();
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id', '用户ID');
                $filter->equal('customer.name', '企业名称');
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
        return Show::make($id, new User(['customer']), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('customer_id');
            $show->field('customer.name', '企业名称');
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
        return Form::make(new User('customer'), function (Form $form) {
            $form->display('id');
            $form->text('name')->required();
            if($form->isCreating()){
                $form->text('password')->saving(function ($value) {
                    return bcrypt($value);
                })->required();
            }
            $form->select('customer_id', '企业名称')->options(function () {
                $list = [];
                $customer = Customer::query()->select("id", "name")->get();
                foreach ($customer as $item) {
                    $list[$item->id] = $item->name;
                }
                return $list;
            })->required();
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
