@extends('PDF.PrnMaster')

@section('mainrep')
  <div  >

    <div style="text-align: center ; margin-bottom: 5px;">
      <label style="font-size: 10pt;">{{$tran_date}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" >من تاريخ : </label>
      <label style="font-size: 10pt;">{{$RepTable->first()->name}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" >كشف حساب الزبون : </label>
    </div>

    <div style="text-align: center ; margin-bottom: 5px;">
      <label style="font-size: 10pt;">{{$raseed}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" >الرصيد </label>
      <label style="font-size: 10pt;">{{$daen}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" >دائن : </label>
      <label style="font-size: 10pt;">{{$mden}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" >مدين : </label>

    </div>

    <table style="border-collapse:collapse;width: 100%"  >
      <thead >

      <tr style="background:lightgray ;">
        <th style="font-size: 7pt;">ملاحظات</th>
        <th style="width: 10%;font-size: 7pt;">دائن</th>
        <th style="width: 10%;font-size: 7pt;">مدين</th>
        <th style="width: 10%;font-size: 7pt;">طريقة الدفع</th>
        <th style="width: 10%;font-size: 7pt;">الرقم الألي</th>
        <th style="width: 12%;font-size: 7pt;">التاريخ</th>
        <th style="width: 12%; font-size: 7pt;">البيان</th>
      </tr>
      </thead>
      <tbody style="margin-bottom: 40px; ">

      @foreach($RepTable as $key=>$item)

        <tr style="border:1px solid ;">
          <td> {{ $item->notes }} </td>
          <td> {{ $item->daen }} </td>
          <td> {{ $item->mden }} </td>
          <td> {{ $item->price_type_name }} </td>
          <td style="text-align: center"> {{ $item->id }} </td>
          <td style="text-align: center"> {{ $item->repDate }} </td>
          <td style="text-align: center"> {{ $item->rec_who->name }} </td>

        </tr>

        <div id="footer" style="height: 50px; width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">

          <label class="page"></label>
          <label> صفحة رقم </label>
        </div>

      @endforeach

      <tr  >
        <td>  </td>
        <td style="color: blue"> {{ number_format($daen,2, '.', ',') }} </td>
        <td style="color: red"> {{ number_format($mden,2, '.', ',') }} </td>
        <td>  الإجمالي</td>

        <td>  </td>
        <td>  </td>


        <td>  </td>
      </tr>


      </tbody>
    </table>


  </div>



@endsection
