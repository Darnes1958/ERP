<?php

namespace App\Livewire;

use App\Models\Customers;
use App\Models\OurCompany;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TopBar extends Component
{
    public $status;
    public $name;
    public function optionSelected()
    {
        User::find(Auth::id())->update(['company' => $this->status]);
        $this->name=Auth::user()->company;
        $this->render();
        return redirect(request()->header('Referer'));

    }
    public function mount(){
        $this->name=Auth::user()->company;
    }

    public function render()
    {
        $company=OurCompany::query()->get();

        return view('livewire.top-bar',['company'=>$company,'name'=>$this->name]);


    }
}
