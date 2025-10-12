@extends('PrnView.PrnMaster')

@section('mainrep')




  <div style="display:flex; flex-direction: row; justify-content:
     center; align-items: center; margin-right: 80px; font-size: 14pt;">
      <label >السادة المحترومون / </label>
      <label> {{$BankName}}</label>

  </div>

  <label style="margin-right: 80px; font-size: 14pt;">تحية طيبة </label>
  <br>

  <div style="display:flex; flex-direction: row; justify-content:
     center; align-items: center; margin-right: 80px; font-size: 12pt;">

    <label >نأمل منكم إيقاف خصم الأقساط من حسابات الزبائن المبينة فالكشف أدناه </label>
  </div>
  <div style="display:flex; flex-direction: row; justify-content:
     center; align-items: center; margin-right: 80px; font-size: 12pt;">


    <label  style=" font-size: 12pt;" >لحساب الشركة التجميعي رقم   </label>
      <label style="font-weight: bold;font-family: DejaVu Sans, sans-serif;
           font-size: 10pt;"> {{$TajAcc}}</label>
      <label style=" font-size: 12pt;">مع رفع الحجز إن وجد  </label>


  </div>
 <br>
  <label style="margin-right: 100px; font-size: 12pt;">نشكركم علي حسن تعاونكم  </label>
  <br>
  <div style="text-align: center;font-size: 12pt;">
    والسلام عليكم ورحمة الله وبركاته
  </div>

  <br>
  <br>
  <div style="text-align: left; margin-left: 100px; font-size: 12pt;">التوقيع ...................  </div>
  <div style="text-align: left; margin-left: 100px; font-size: 12pt;"> مفوض الشركة  </div>

    <br>

  <table  width="100%"   align="right" >

    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >
        <th style="width: 8%">ت</th>
        <th style="width: 14%">رقم العقد</th>
        <th style="width: 20%">رقم الحساب</th>
        <th >الاسم</th>
        <th style="width: 14%">القسط</th>
        <th style="width: 14%">التاريخ</th>


    </tr>
    </thead>
    <tbody style="margin-bottom: 40px; ">
    @php $i=0 @endphp
    @foreach($RepTable as $key => $item)
      <tr >
          <td style="text-align: center;">{{++$i}}</td>
          <td> {{ $item->id }} </td>
          <td > {{ $item->acc }} </td>
          <td> {{ $item->Customer->name }} </td>
          <td> {{ $item->kst }} </td>
        <td style="text-align: center;"> {{ $item->Stop->stop_date }} </td>
      </tr>

    @endforeach
    <tr >
    <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
    <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
    <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
    <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
    <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
    </tr>
    </tbody>

  </table>
  </div>
  </div>


@endsection
