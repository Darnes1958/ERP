@extends('PDF.PrnMaster')

@section('mainrep')
  <div  >

    <div style="text-align: center">
     @if($RepDate1 && !$RepDate2)
      <label style="font-size: 10pt;">{{$RepDate1}}</label>
      <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   من تاريخ : </label>
     @endif
     @if($RepDate2 && !$RepDate1)
         <label style="font-size: 10pt;">{{$RepDate2}}</label>
         <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   حتي تاريخ : </label>
     @endif
     @if($RepDate1 && $RepDate2)
         <label style="font-size: 10pt;">{{$RepDate2}}</label>
         <label style="font-size: 14pt;margin-right: 12px;" > حتي تاريخ : </label>
         <label style="font-size: 10pt;">{{$RepDate1}}</label>
         <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   من تاريخ : </label>
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
        <th >نقطة البيع</th>
      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php $sumtot=0;$sumcash=0;$sumnot_cash=0; @endphp
      @foreach($BuyTable as $key=>$item)
        <tr class="font-size-12">
          <td> {{number_format($item->baky, 2, '.', ',')}} </td>
          <td> {{number_format($item->pay, 2, '.', ',')}} </td>
          <td> {{number_format($item->tot, 2, '.', ',')}} </td>
          <td >{{$item->name}}  </td>
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
        <td style="font-weight:normal;">الإجمــــــــالي  </td>
      </tr>

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
        <th >نقطة البيع</th>
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
          <td> {{$item->name}}  </td>
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

        <td style="font-weight:normal;">الإجمــــــــالي  </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


      </tbody>
    </table>






    <label style="font-size: 14pt;margin-right: 12px;" >الموردين</label>
      <table style=" width:80%"   align="right" >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 14%;">دفع</th>
              <th style="width: 14%;">قبض</th>
              <th >الحساب المصرفي / الخزينة</th>
              <th style="width: 20%;">طريقة الدفع</th>
              <th style="width: 20%;">البيان</th>

          </tr>
      </thead>
      <tbody >
      @php $sumval=0;$sumexp=0; @endphp
      @foreach($SuppTable as $key=>$item)

        <tr class="font-size-12">
          <td> {{number_format($item->exp, 2, '.', ',')}} </td>
          <td> {{number_format($item->val, 2, '.', ',')}} </td>
            @if($item->accName)
                <td> {{$item->accName}}  </td>
            @else
                <td> {{$item->kazenaName}}  </td>
            @endif
          <td> {{$item->name}}  </td>
          <td >{{$item->rec_who->name}}  </td>
        </tr>
        <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
          <label class="page"></label>
          <label> صفحة رقم </label>
        </div>
        @php $sumval+=$item->val;$sumexp+=$item->exp; @endphp
      @endforeach
      <tr class="font-size-12 " style="font-weight: bold">
        <td> {{number_format($sumexp, 2, '.', ',')}} </td>
        <td> {{number_format($sumval, 2, '.', ',')}} </td>
        <td>   </td>
          <td>   </td>
        <td style="font-weight:normal;">الإجمــــــــالي  </td>
      </tr>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      </tbody>
    </table>

      @if($CustTable)
      <label style="font-size: 14pt;margin-right: 12px;" >الزبائن</label>
      <table style=" width:80%"   align="right" >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th style="width: 14%;">دفع</th>
              <th style="width: 14%;">قبض</th>
              <th >الحساب المصرفي / الخزينة</th>
              <th style="width: 20%;">طريقة الدفع</th>
              <th style="width: 20%;">البيان</th>

          </tr>
          </thead>
          <tbody >
          @php $sumval=0;$sumexp=0; @endphp
          @foreach($CustTable as $key=>$item)
              <tr class="font-size-12">
                  <td> {{number_format($item->exp, 2, '.', ',')}} </td>
                  <td> {{number_format($item->val, 2, '.', ',')}} </td>
                  @if($item->accName)
                      <td> {{$item->accName}}  </td>
                  @else
                      <td> {{$item->kazenaName}}  </td>
                  @endif
                  <td> {{$item->name}}  </td>
                  <td >{{$item->rec_who->name}}  </td>
              </tr>
              <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
                  <label class="page"></label>
                  <label> صفحة رقم </label>
              </div>
              @php $sumval+=$item->val;$sumexp+=$item->exp; @endphp
          @endforeach
          <tr class="font-size-12 " style="font-weight: bold">
              <td> {{number_format($sumexp, 2, '.', ',')}} </td>
              <td> {{number_format($sumval, 2, '.', ',')}} </td>
              <td>   </td>
              <td>   </td>
              <td style="font-weight:normal;">الإجمــــــــالي  </td>
          </tr>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          </tbody>
      </table>
      @endif
      @if($tar_buy)
          <label style="font-size: 14pt;margin-right: 12px;" >ترجيع مشتريات</label>
          <table style=" width:80%"   align="right" >
              <thead style="  margin-top: 8px;">
              <tr style="background:lightgray">
                  <th style="width: 16%;">الإجمالي</th>
                  <th style="width: 26%;">التاريخ</th>
              </tr>
              </thead>
              <tbody >
              @php $sumval=0; @endphp
              @foreach($tar_buy as $key=>$item)
                  <tr class="font-size-12">
                      <td> {{number_format($item->sub_tot, 2, '.', ',')}} </td>
                      <td> {{$item->tar_date}}  </td>
                  </tr>
                  <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
                      <label class="page"></label>
                      <label> صفحة رقم </label>
                  </div>
                  @php $sumval+=$item->sub_tot; @endphp
              @endforeach
              <tr class="font-size-12 " style="font-weight: bold">
                  <td> {{number_format($sumval, 2, '.', ',')}} </td>

                  <td style="font-weight:normal;">الإجمــــــــالي  </td>
              </tr>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              </tbody>
          </table>
      @endif
      @if($tar_sell)
          <label style="font-size: 14pt;margin-right: 12px;" >ترجيع مبيعات</label>
          <table style=" width:80%"   align="right" >
              <thead style="  margin-top: 8px;">
              <tr style="background:lightgray">
                  <th style="width: 16%;">الإجمالي</th>
                  <th style="width: 26%;">التاريخ</th>
              </tr>
              </thead>
              <tbody >
              @php $sumval=0; @endphp
              @foreach($tar_sell as $key=>$item)
                  <tr class="font-size-12">
                      <td> {{number_format($item->sub_tot, 2, '.', ',')}} </td>
                      <td> {{$item->tar_date}}  </td>
                  </tr>
                  <div id="footer" style=" width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
                      <label class="page"></label>
                      <label> صفحة رقم </label>
                  </div>
                  @php $sumval+=$item->sub_tot; @endphp
              @endforeach
              <tr class="font-size-12 " style="font-weight: bold">
                  <td> {{number_format($sumval, 2, '.', ',')}} </td>

                  <td style="font-weight:normal;">الإجمــــــــالي  </td>
              </tr>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              </tbody>
          </table>
      @endif

      @if($masr)
          <label style="font-size: 14pt;margin-right: 12px;" >المصروفات</label>
          <table style=" width:80%"   align="right" >
              <thead style="  margin-top: 8px;">
              <tr style="background:lightgray">
                  <th style="width: 14%;">المبلغ</th>
                  <th style="width: 20%;">دفعت من</th>
                  <th style="width: 20%;">البيان</th>
              </tr>
              </thead>
              <tbody >
              @php $sumval=0; @endphp
              @foreach($masr as $key=>$item)
                  <tr class="font-size-12">
                      <td> {{number_format($item->val, 2, '.', ',')}} </td>
                      <td> {{$item->acc_name}} </td>
                      <td> {{$item->name}}  </td>
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
                  <td style="font-weight:normal;">الإجمــــــــالي  </td>
              </tr>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              </tbody>
          </table>
      @endif
      @if($salary)
          <label style="font-size: 14pt;margin-right: 12px;" >المرتبات</label>
          <table style=" width:80%"   align="right" >
              <thead style="  margin-top: 8px;">
              <tr style="background:lightgray">
                  <th style="width: 14%;">المبلغ</th>
                  <th style="width: 20%;">البيان</th>
              </tr>
              </thead>
              <tbody >
              @php $sumval=0; @endphp
              @foreach($salary as $key=>$item)
                  <tr class="font-size-12">
                      <td> {{number_format($item->val, 2, '.', ',')}} </td>
                      <td> {{$item->tran_type}}  </td>
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

                  <td style="font-weight:normal;">الإجمــــــــالي  </td>
              </tr>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>

              </tbody>
          </table>
      @endif
  </div>
@endsection
