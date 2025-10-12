@extends('PrnView.PrnCont')

@section('mainrep')
    <div style="position: relative; " >
<div >
    <div style="text-align: right;display: inline-flex;position: absolute;right: 0;top: 0">
        <label style="padding-left: 4px;"> رقم العقد </label>
        <label style="padding-left: 4px;" > {{$res->id}} </label>
    </div>
    <div style="text-align: left;display: inline-flex;position: absolute;top: 0;left: 0">
       <label style="padding-left: 4px;"> تاريخ العقد </label>
       <label style="padding-left: 4px;" > {{$res->sul_begin}} </label>
   </div>


</div>

<div style="text-align: center;font-size: 18pt;">
    <label  > {{$cus->CompanyName}} </label>
</div>
<div style="text-align: center;font-size: 18pt;">
    <label  > {{$cus->CompanyNameSuffix}} </label>
</div>
        <div style="display: inline-flex;">
            <label style="text-align: center;font-size: 18pt;padding-right: 300px;" > عقد بيع لأجل </label>
        </div>
<div style="text-align: right;font-size: 18pt;color: #bf800c">
    <label  > أولا بيانات تعبأ من قبل المحل </label>
</div>

<div  style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;">الإخوة مصرف / </label>
    <label id="mainlabel" style="width: 350px;">{{$arr['BankName']}}</label>
    <label  style="display:inline-block;">نرجو منكم إستقطاع الأقساط الشهرية المترتبة علي</label>
</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;">الأخ / </label>
    <label  id="mainlabel" style="width: 160px;">{{$res->Customer->name}}</label>
    <label  style="display:inline-block;">لصالح هذه الشركة علماً بان القيمة الإجمالية المترتبة علي هذه الاقساط</label>
    <label  id="mainlabel" style="width: 80px;">{{$res->sul}}</label>
    <label  style="display:inline-block;">دينار ليبي</label>
</div>

<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;">علي أن يبدا الإستقطاع من شهر </label>
    <label  id="mainlabel" style="width: 70px;">{{$arr['mindate']}}</label>
    <label  style="display:inline-block;">إلي شهر</label>
    <label  id="mainlabel" style="width: 70px;">{{$arr['maxdate']}}</label>
    <label  style="display:inline-block;">عدد الاشهر </label>
    <label  id="mainlabel" style="width: 30px;">{{$res->kst_count}}</label>
    <label  style="display:inline-block;">و قيمة الإستقطاع الشهري</label>
    <label  id="mainlabel" style="width: 85px;">{{$res->kst}}</label>
</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;">وذلك لحساب الشركة التجميعي رقم </label>
    <label  id="mainlabel" style="width: 160px;">{{$arr['TajAcc']}}</label>
    <label  style="display:inline-block;">مصرف </label>
    <label  id="mainlabel" style="width: 300px;">{{$arr['BankName']}}</label>



</div>
        <div style="text-align: right;font-size: 11pt;">
            <label  style="display:inline-block;padding-right: 4px;">علي أن يتحمل الزبون أتعاب المصرف</label>
        </div>


<div style="text-align: right;font-size: 18pt;color: #bf800c">
    <label  > ثانياً بيانات تعبأ من قبل الزبون </label>
</div>
        <div style="text-align: right;font-size: 11pt;">
            <label  style="display:inline-block;padding-right: 4px;">انا الموقع أدناه </label>
            <label  id="mainlabel" style="width: 300px;">{{$res->Customer->name}}</label>
            <label  style="display:inline-block;">بطاقة شخصية رقم </label>
            <label  id="mainlabel" style="width: 160px;">{{$res->Customer->card_no}}</label>
        </div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;">اخول مصرف  </label>
    <label  id="mainlabel" style="width: 360px;">{{$arr['BankName']}}</label>
    <label  style="display:inline-block;">باستقطاع مبلغ وقدره </label>
    <label  id="mainlabel" style="width: 120px;">{{$res->kst}}</label>
</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;">من حسابي رقم </label>
    <label  id="mainlabel" style="width: 200px;">{{$res->acc}}</label>
    <label  style="display:inline-block;">لصالح الحساب الخاص بالشركة رقم  </label>
    <label  id="mainlabel" style="width: 200px;">{{$arr['TajAcc']}}</label>
</div>

<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;">وذلك باستقطاع المبلغ المذكور من حسابي طرفكم شهرياً علي أن يبدا الإستقطاع من شهر </label>
    <label  id="mainlabel" style="width: 100px;">{{$res->sul_begin}}</label>

</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;"> إلي أن تصل قيمة الإستقطاع مبلغ وقدره </label>
    <label  id="mainlabel" style="width: 300px;">{{$res->sul}}</label>

</div>


<div style="text-align: right;font-size: 11pt;">

    <label  style="display:inline-block;padding-right: 4px;">وأن أتحمل أتعاب الخدمات المصرفية ولا يحق لي إيقاف الإستقطاع إلا بموافقة خطية من الشركة وذلك إقرار مني بذلك  </label>
</div>

<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;"> الإسم </label>
    <label  id="mainlabel" style="width: 300px;">{{$res->Customer->name}}</label>
    <label  style="display:inline-block;padding-right: 4px;"> التوقيع </label>
    <label  id="mainlabel" style="width: 280px;"></label>
</div>
        <div style="text-align: right;font-size: 11pt;">


            <label  style="display:inline-block;padding-right: 4px;"> مدار </label>
               @if($res->Customer->libyana) <label  id="mainlabel" style="width: 300px;">{{$res->Customer->libyana}}</label>
               @else <label  id="mainlabel" style="width: 300px;"></label>
               @endif

            <label  style="display:inline-block;padding-right: 4px;"> لبيانا </label>

            @if($res->Customer->mdar) <label  id="mainlabel" style="width: 300px;">{{$res->Customer->mdar}}</label>
            @else <label  id="mainlabel" style="width: 300px;"></label>
            @endif
        </div>


<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;"> ملاحظات /  </label>
    <label  id="mainlabel" style="width: 600px;"></label>

</div>

<div style="text-align: right;font-size: 18pt;color: #bf800c">
    <label  > ثالثاً بيانات تعبأ من قبل المصرف </label>
</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;"> يفيد مصرف </label>
    <label  id="mainlabel" style="width: 200px;"></label>
    <label  style="display:inline-block;">فرع  </label>
    <label  id="mainlabel" style="width: 100px;"></label>
    <label  style="display:inline-block;"> بالموافقة علي خصم الأقساط الشهرية من حساب </label>




</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;"> الاخ </label>
    <label  id="mainlabel" style="width: 200px;"></label>
    <label  style="display:inline-block;">رقم  </label>
    <label  id="mainlabel" style="width: 200px;"></label>
    <label  style="display:inline-block;"> في حال توفر الرصيد أو ورود </label>
</div>
<div style="text-align: right;font-size: 11pt;">

    <label  style="display:inline-block;padding-right: 4px;"> المرتبات او السحب علي المكشوف بعد خصم الإستقطاعات والإلتزامات الخاصة بالمصرف وقيدها إلي حساب </label>
</div>
<div style="text-align: right;font-size: 11pt;">
    <label  style="display:inline-block;padding-right: 4px;"> الشركة رقم </label>
    <label  id="mainlabel" style="width: 200px;"></label>
    <label  style="display:inline-block;">طرف مصرف  </label>
    <label  id="mainlabel" style="width: 100px;"></label>
    <label  style="display:inline-block;"> فرع </label>
    <label  id="mainlabel" style="width: 100px;"></label>
</div>
        <div style="position: fixed;  bottom: 40px;  left: 80px;font-size: 18pt;color: #bf800c">
            <label   > إعتماد الشركة </label>
        </div>

        <div style="position: fixed;bottom: 40px;       right: 80px;font-size: 18pt;color: #bf800c" >

            <label  > إعتماد المصرف </label>
        </div>

        <div style="position: fixed;bottom: 20px;       right: 40px;font-size: 18pt;color: #bf800c" >
            <label  id="mainlabel" style="width: 200px;"></label>

        </div>
        <div style="position: fixed;  bottom: 20px;  left: 40px;font-size: 18pt;color: #bf800c">
            <label  id="mainlabel" style="width: 200px;"></label>
        </div>



</div>



@endsection







