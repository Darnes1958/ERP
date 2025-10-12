<?php
namespace App\Livewire\Forms;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Livewire\Attributes\Rule;
use Livewire\Form;


class BankForm extends Form
{
    #[Rule('required',message: 'يجب ادخال الاسم')]
    public $BankName = '';
    #[Rule('required',message: 'يجب اختيار المصرف التجميعي')]
    public $taj_id = '';
    public $user_id='';


}

