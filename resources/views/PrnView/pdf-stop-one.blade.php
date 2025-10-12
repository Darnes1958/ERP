@extends('PrnView.PrnMasterOne')

@section('mainrep')

<div style="display:flex; flex-direction: row; justify-content:
     center; align-items: center; margin-right: 80px; font-size: 16pt;">
    <label >السادة المحترومون / </label>
    <label> {{$BankName}}</label>
</div>

<label style="margin-right: 80px; font-size: 16pt;">تحية طيبة </label>
<br>
<br>
<div style="display:flex; flex-direction: row; justify-content:
     center; align-items: center; margin-right: 80px; font-size: 14pt;">

    <label >نأمل منكم إيقاف خصم الأقساط من حساب السيد / </label>
    <label style="font-weight: bold;font-family: DejaVu Sans, sans-serif;
           font-size: 11pt;"> {{$record->Customer->name}}</label>
</div>

<br>
<br>
<div style="display:flex; flex-direction: row; font-size: 14pt;">
    <label >حساب جاري رقم  </label>
    <label style="font-weight: bold;font-family: DejaVu Sans, sans-serif;
           font-size: 11pt;"> {{$record->acc}}</label>

</div>

@if($record->kst !=0)
<div style="display:flex; flex-direction: row;  font-size: 14pt;">

    <label >وقيمة القسط  </label>
    <label style="font-weight: bold;font-family: DejaVu Sans, sans-serif;
           font-size: 11pt;"> {{$record->kst}}</label>
</div>
@endif

<div style="display:flex; flex-direction: row;  font-size: 14pt;">

    <label >لحساب الشركة التجميعي رقم   </label>
    <label style="font-weight: bold;font-family: DejaVu Sans, sans-serif;
           font-size: 11pt;"> {{$TajAcc}}</label>
</div>
<div style="display:flex; flex-direction: row;  font-size: 14pt;">

    <label >اعتباراً من تاريخ   </label>
    <label style="font-weight: bold;font-family: DejaVu Sans, sans-serif;
           font-size: 11pt;"> {{$record->Stop->stop_date}}</label>
</div>

<br>

<label style="margin-right: 80px; font-size: 14pt;">مع رفع الحجز إن وجد  </label>
<br><br>
<label style="margin-right: 100px; font-size: 14pt;">نشكركم علي حسن تعاونكم  </label>
<br><br>
<div style="text-align: center;font-size: 14pt;">
   والسلام عليكم ورحمة الله وبركاته
</div>

<br>
<br><br>
<div style="text-align: left; margin-left: 100px; font-size: 14pt;">التوقيع ...................  </div>
<div style="text-align: left; margin-left: 100px;font-size: 14pt;">    مفوض الشركة /      </div>


@endsection
