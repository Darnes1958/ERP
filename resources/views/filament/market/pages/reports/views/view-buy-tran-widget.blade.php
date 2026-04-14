<div>

    <div>
        <label class="font-extrabold text-blue-600">تاريخ الفاتورة : </label>
        <label>{{$record->order_date}}</label>
    </div>

    <div>
        <label class="font-extrabold text-blue-600">المورد : </label>
        <label>{{$record->Supplier->name}}</label>
    </div>
    <div>
        <label class="font-extrabold text-blue-600">طريقة الدفع : </label>
        <label>{{$record->Price_type->name}}</label>
    </div>
    <div>
        <label class="font-extrabold text-blue-600">مكان التخزين :</label>
        <label>{{$record->Place->name}}</label>
    </div>
    @livewire(\App\Livewire\widget\BuyTran::class, ["buy_id" => $buy_id])
</div>
