@extends('PDF.PrnMaster')

@section('mainrep')
  <div  >

    <div style="text-align: center">
     @if($RepDate1 && !$RepDate2)
      <label style="font-size: 10pt;">{{$RepDate1}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" > الحركة اليومية   من تاريخ : </label>
     @endif
     @if($RepDate2 && !$RepDate1)
         <label style="font-size: 10pt;">{{$RepDate2}}</label>
         <label style="font-size: 14pt;margin-right: 12px;" > الحركة اليومية   حتي تاريخ : </label>
     @endif
     @if($RepDate1 && $RepDate2)
         <label style="font-size: 10pt;">{{$RepDate2}}</label>
         <label style="font-size: 14pt;margin-right: 12px;" > حتي تاريخ : </label>
         <label style="font-size: 10pt;">{{$RepDate1}}</label>
         <label style="font-size: 14pt;margin-right: 12px;" > الحركة اليومية   من تاريخ : </label>
     @endif

    </div>


          @if($place_name !=' ')
          <div style="text-align: center">
              <label style="font-size: 10pt;">{{$place_name}}</label>
              <label style="font-size: 14pt;margin-right: 12px;" > المكان : </label>
          </div>
          @endif


    <label style="font-size: 14pt;margin-right: 12px;" >مشتريات </label>
    <table  width="100%"   align="right" >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
        <th style="width: 12%;">الباقي</th>
        <th style="width: 12%;">المدفوع</th>
        <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">التاريخ</th>
          <th style="width: 12%;">رقم الفاتورة</th>
          <th >اسم المورد</th>

      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php $sumtot=0;$sumcash=0;$sumnot_cash=0; @endphp
      @foreach($BuyTable as $key=>$item)
        <tr class="font-size-12">
          <td> {{number_format($item->baky, 2, '.', ',')}} </td>
          <td> {{number_format($item->pay, 2, '.', ',')}} </td>
          <td> {{number_format($item->tot, 2, '.', ',')}} </td>
            <td>{{$item->order_date}}</td>
            <td>{{$item->id}}</td>
            <td> {{$item->Supplier->name}}  </td>

        </tr>
        <div id="footer" style="height: 50px; width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
          <label class="page"></label>
          <label> صفحة رقم </label>
        </div>
        @php $sumtot+=$item->tot;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
        <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
        <td> {{number_format($sumcash, 2, '.', ',')}} </td>
        <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          <td></td>
          <td></td>
        <td style="font-weight:normal;">الإجمــــــــالي  </td>
      </tr>

      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>



      </tbody>
    </table>

    <label style="font-size: 14pt;margin-right: 12px;" >مبيعات  </label>
    <table  width="100%"   align="right" >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
        <th style="width: 12%;">الباقي</th>
        <th style="width: 12%;">المدفوع</th>
        <th style="width: 12%;">الإجمالي</th>
          <th style="width: 12%;">التاريخ</th>
          <th style="width: 12%;">رقم الفاتورة</th>
        <th >اسم الزبون</th>
      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php ;$sumtot=0;$sumcash=0;$sumnot_cash=0;

      @endphp
      @foreach($SellTable as $key=>$item)
        <tr class="font-size-12">
          <td> {{number_format($item->baky, 2, '.', ',')}} </td>
          <td> {{number_format($item->pay, 2, '.', ',')}} </td>
          <td> {{number_format($item->total, 2, '.', ',')}} </td>
            <td>{{$item->order_date}}</td>
          <td>{{$item->id}}</td>
          <td> {{$item->Customer->name}}  </td>
        </tr>
        <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
          <label class="page"></label>
          <label> صفحة رقم </label>
        </div>
        @php $sumtot+=$item->total;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
      @endforeach

      <tr class="font-size-12 " style="font-weight: bold">
        <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
        <td> {{number_format($sumcash, 2, '.', ',')}} </td>
        <td> {{number_format($sumtot, 2, '.', ',')}} </td>
        <td></td>
        <td></td>
        <td style="font-weight:normal;">الإجمــــــــالي  </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


      </tbody>
    </table>

    <label style="font-size: 14pt;margin-right: 12px;" >الموردين</label>
      <table   align="right" >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 10%;">المبلغ</th>
              <th style="width: 20%;">الخزينة</th>
              <th style="width: 26%;">الحساب المصرفي</th>

              <th >المورد</th>
              <th style="width: 10%;text-align: center;">البيان</th>
              <th style="width: 11%;text-align: center;">التاريخ</th>

          </tr>
      </thead>
      <tbody >
      @php $sumval=0; @endphp
      @foreach($SuppTable as $key=>$item)

        <tr class="font-size-12">

            <td> {{number_format($item->val, 2, '.', ',')}} </td>
            @if( $item->Kazena)
                <td> {{$item->Kazena->name}}  </td>
            @else
                <td></td>
            @endif
            @if( $item->Acc)
                <td> {{$item->Acc->name}}  </td>
            @else
                <td></td>
            @endif

            <td > {{$item->Supplier->name}}  </td>
            <td style="text-align: center;">{{$item->rec_who->name}}  </td>
            <td style="text-align: center;">{{$item->receipt_date}}  </td>
        </tr>
        <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
          <label class="page"></label>
          <label> صفحة رقم </label>
        </div>
        @php $sumval+=$item->val; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
          <td> {{number_format($sumval, 2, '.', ',')}} </td>
          <td>   </td>

          <td>   </td>
          <td>   </td>
          <td>   </td>
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      </tbody>
    </table>

      @if($CustTable)
      <label style="font-size: 14pt;margin-right: 12px;" >الزبائن</label>
      <table    align="right" >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">

              <th style="width: 10%;">المبلغ</th>
              <th style="width: 20%;">الخزينة</th>
              <th style="width: 26%;">الحساب المصرفي</th>

              <th >الزبون</th>
              <th style="width: 10%;text-align: center;">البيان</th>
              <th style="width: 11%;text-align: center;">التاريخ</th>

          </tr>
          </thead>
          <tbody >
          @php $sumval=0; @endphp
          @foreach($CustTable as $key=>$item)
              <tr class="font-size-12">

                  <td> {{number_format($item->val, 2, '.', ',')}} </td>
                  @if( $item->Kazena)
                  <td> {{$item->Kazena->name}}  </td>
                  @else
                      <td></td>
                  @endif
                  @if( $item->Acc)
                  <td> {{$item->Acc->name}}  </td>
                  @else
                      <td></td>
                  @endif

                  <td > {{$item->Customer->name}}  </td>
                  <td style="text-align: center;">{{$item->rec_who->name}}  </td>
                  <td style="text-align: center;">{{$item->receipt_date}}  </td>
              </tr>
              <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
                  <label class="page"></label>
                  <label> صفحة رقم </label>
              </div>
              @php $sumval+=$item->val; @endphp
          @endforeach
          <tr class="font-size-12 " style="font-weight: bold">

              <td> {{number_format($sumval, 2, '.', ',')}} </td>
              <td>   </td>

              <td>   </td>
              <td>   </td>
              <td>   </td>
              <td style="font-weight:normal;">الإجمــــــــالي  </td>
          </tr>

          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>

          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>

          </tbody>
      </table>
      @endif


  </div>



@endsection
