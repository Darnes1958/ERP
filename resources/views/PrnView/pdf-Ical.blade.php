@extends('PrnView.PrnMaster')

@section('mainrep')
    @php
        use Illuminate\Support\Number;
 @endphp
    <div class=" border-2 border-gray-600 rounded-xl p-4">
        <br>
    <div style="text-align: center">
        <label class="text-lg font-extrabold">إيصال استلام رقم </label>
        <label class="bg-gray-100 text-lg font-extrabold">{{$res->id}}</label>
    </div>

    <br>
    <br>

    <div >
        <label class="text-lg font-medium">بتاريخ :&nbsp;&nbsp; </label>
        <label class="text-lg bg-gray-50">{{$res->receipt_date}}</label>
    </div>
    <br>
    <div>
        <label class="text-lg " >استلمت من السيد :&nbsp;&nbsp; </label>
        <label class="text-lg font-extrabold bg-gray-50">{{$res->Customer->name}}</label>
    </div>
    <br>
    <div>
        <label class="text-lg ">مبلغ وقدره :&nbsp;&nbsp; </label>
        <label class="text-lg font-extrabold bg-gray-50">{{$res->val}}</label>
        <label>( فقط {{Illuminate\Support\Number::spell($res->val, locale: 'ar')}} دينار )</label>
    </div>

    <br>
    <br>

    <div style="position: fixed;    left: 160px;font-size: 18pt;">
        <label  >المستلم</label>
    </div>
    <br>
    <br>
    <div style="position: fixed;    left: 80px;font-size: 18pt;">
        <label   style="width: 200px;">...........................</label>
    </div>
        <br>
        <br>

    </div>

@endsection
