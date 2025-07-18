@extends('PDF.PrnMasterSpatie')

@section('mainrep')



        <div style="text-align: center">
            <label style="font-size: 14pt;margin-right: 12px;" > فواتير المبيعات </label>
            @if($arr['RepDate1'] && !$arr['RepDate2'])
                <label style="font-size: 14pt;margin-right: 12px;" >    من تاريخ : </label>
                <label style="font-size: 10pt;">{{$arr['RepDate1']}}</label>
            @endif
            @if($arr['RepDate2'] && !$arr['RepDate1'])
                <label style="font-size: 14pt;margin-right: 12px;" >    حتي تاريخ : </label>
                <label style="font-size: 10pt;">{{$arr['RepDate2']}}</label>
            @endif
            @if($arr['RepDate1'] && $arr['RepDate2'])
                <label style="font-size: 14pt;margin-right: 12px;" >    من تاريخ : </label>
                <label style="font-size: 10pt;">{{$arr['RepDate1']}}</label>
                <label style="font-size: 14pt;margin-right: 12px;" > حتي تاريخ : </label>
                <label style="font-size: 10pt;">{{$arr['RepDate2']}}</label>
            @endif

        </div>

            @if($arr['place'])
                <div >
                 <label style="font-size: 14pt;margin-right: 12px;" >    نقطة البيع :  </label>
                 <label style="font-size: 10pt;">{{$arr['place']}}</label>
                </div>
            @endif
        @if($arr['customer'])
            <div >
                <label style="font-size: 14pt;margin-right: 12px;" >    للزبون :  </label>
                <label style="font-size: 10pt;">{{$arr['customer']}}</label>
            </div>
        @endif
        @if($arr['active']!='الكل')
            <div >
                <label style="font-size: 14pt;margin-right: 12px;" >    نوع الفواتير :  </label>
                <label style="font-size: 10pt;">{{$arr['active']}}</label>
            </div>
        @endif


    <div  >
    <table  width="100%"   align="right" >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          <th >اسم الزبون</th>
          <th style="width: 12%;">رقم الفاتورة</th>
          <th style="width: 12%;">التاريخ</th>
          <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">المدفوع</th>
          <th style="width: 12%;">الباقي</th>
      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php ;$sumtot=0;$sumcash=0;$sumnot_cash=0;

      @endphp
      @foreach($res as $key=>$item)
        <tr class="font-size-14">
            <td> {{$item->Customer->name}}  </td>
            <td>{{$item->id}}</td>
            <td>{{$item->order_date}}</td>
            <td> {{number_format($item->total, 2, '.', ',')}} </td>
            <td> {{number_format($item->pay, 2, '.', ',')}} </td>
            <td> {{number_format($item->baky, 2, '.', ',')}} </td>
        </tr>

        @php $sumtot+=$item->total;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
      @endforeach

      <tr class="font-size-14 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          <td></td>
          <td></td>
          <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          <td> {{number_format($sumcash, 2, '.', ',')}} </td>
          <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


      </tbody>
    </table>




  </div>



@endsection
