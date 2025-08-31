@extends('PDF.PrnMaster')

@section('mainrep')
  <div  >
      <div style="text-align: center">
          @if($arr['RepDate1'] && !$arr['RepDate2'])
              <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   من تاريخ : </label>
              <label style="font-size: 10pt;">{{$arr['RepDate1']}}</label>
          @endif
          @if($arr['RepDate2'] && !$arr['RepDate1'])
                  <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   حتي تاريخ : </label>
              <label style="font-size: 10pt;">{{$arr['RepDate2']}}</label>

          @endif
          @if($arr['RepDate1'] && $arr['RepDate2'])
                  <label style="font-size: 14pt;margin-right: 12px;" >خلاصة الحركة اليومية   من تاريخ : </label>
              <label style="font-size: 10pt;">{{$arr['RepDate1']}}</label>
                  <label style="font-size: 14pt;margin-right: 12px;" > حتي تاريخ : </label>
              <label style="font-size: 10pt;">{{$arr['RepDate2']}}</label>
          @endif

      </div>


    </div>
  @if($arr['place_name'] !=' ')
      <div style="text-align: center">
          <label style="font-size: 10pt;">{{$arr['place_name']}}</label>
          <label style="font-size: 14pt;margin-right: 12px;" > المكان : </label>
      </div>
  @endif

  <div>
      <label style="font-size: 14pt;margin-right: 12px;" >مشتريات </label>
      <table  width="100%"   align="right" >
          <thead style="  margin-top: 8px;">
          <tr style="background:lightgray">
              <th >نقطة البيع</th>
              <th style="width: 12%;">الإجمالي</th>
              <th style="width: 12%;">المدفوع</th>
              <th style="width: 12%;">الباقي</th>
          </tr>
          </thead>
          <tbody id="addRow" class="addRow">
          @php $sumtot=0;$sumcash=0;$sumnot_cash=0; @endphp
          @foreach($res as $key=>$item)
              <tr class="font-size-12">
                  <td >{{$item->name}}  </td>
                  <td> {{number_format($item->tot, 2, '.', ',')}} </td>
                  <td> {{number_format($item->pay, 2, '.', ',')}} </td>
                  <td> {{number_format($item->baky, 2, '.', ',')}} </td>
              </tr>

              @php $sumtot+=$item->tot;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
          @endforeach
          <tr class="font-size-12 " style="font-weight: bold">
              <td style="font-weight:normal;">الإجمــــــــالي  </td>
              <td> {{number_format($sumtot, 2, '.', ',')}} </td>
              <td> {{number_format($sumcash, 2, '.', ',')}} </td>
              <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
          </tr>

          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
          <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


          </tbody>
      </table>
  </div>

<br>

<div>
    <label style="font-size: 14pt;margin-right: 12px;" >مبيعات  </label>
    <table  width="100%"   align="right" >
        <thead style="  margin-top: 8px;">
        <tr style="background:lightgray">
            <th >نقطة البيع</th>
            <th style="width: 12%;">الإجمالي</th>
            <th style="width: 12%;">المدفوع</th>
            <th style="width: 12%;">الباقي</th>
        </tr>
        </thead>
        <tbody id="addRow" class="addRow">
        @php ;$sumtot=0;$sumcash=0;$sumnot_cash=0;

        @endphp
        @foreach($arr['SellTable'] as $key=>$item)
            <tr class="font-size-12">
                <td> {{$item->name}}  </td>
                <td> {{number_format($item->total, 2, '.', ',')}} </td>
                <td> {{number_format($item->pay, 2, '.', ',')}} </td>
                <td> {{number_format($item->baky, 2, '.', ',')}} </td>
            </tr>

            @php $sumtot+=$item->total;$sumcash+=$item->pay;$sumnot_cash+=$item->baky; @endphp
        @endforeach

        <tr class="font-size-12 " style="font-weight: bold">
            <td style="font-weight:normal;">الإجمــــــــالي  </td>
            <td> {{number_format($sumtot, 2, '.', ',')}} </td>
            <td> {{number_format($sumcash, 2, '.', ',')}} </td>
            <td> {{number_format($sumnot_cash, 2, '.', ',')}} </td>
        </tr>
        <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
        <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
        <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
        <td style="border-bottom: none;border-left: none;border-right: none;"> </td>


        </tbody>
    </table>
</div>

  <br>

<div>
    <label style="font-size: 14pt;margin-right: 12px;" >الموردين</label>
    <table style=" width:100%"   align="right" >
        <thead style="  margin-top: 8px;">
        <tr style="background:lightgray">
            <th style="width: 20%;">البيان</th>
            <th style="width: 20%;">طريقة الدفع</th>
            <th >الحساب المصرفي / الخزينة</th>
            <th style="width: 14%;">قبض</th>
            <th style="width: 14%;">دفع</th>
        </tr>
        </thead>
        <tbody >
        @php $sumval=0;$sumexp=0; @endphp
        @foreach($arr['SuppTable'] as $key=>$item)

            <tr class="font-size-12">
                <td >{{$item->rec_who->name}}  </td>
                <td> {{$item->name}}  </td>
                @if($item->accName)
                    <td> {{$item->accName}}  </td>
                @else
                    <td> {{$item->kazenaName}}  </td>
                @endif
                <td> {{number_format($item->val, 2, '.', ',')}} </td>
                <td> {{number_format($item->exp, 2, '.', ',')}} </td>
            </tr>

            @php $sumval+=$item->val;$sumexp+=$item->exp; @endphp
        @endforeach
        <tr class="font-size-12 " style="font-weight: bold">

            <td style="font-weight:normal;">الإجمــــــــالي  </td>
            <td>   </td>
            <td>   </td>
            <td> {{number_format($sumval, 2, '.', ',')}} </td>
            <td> {{number_format($sumexp, 2, '.', ',')}} </td>
        </tr>

        </tbody>
    </table>
</div>

  <br>

          <div>
              <label style="font-size: 14pt;margin-right: 12px;" >الزبائن</label>
              <table style=" width:100%"   align="right" >
                  <thead style="  margin-top: 8px;">
                  <tr style="background:lightgray">
                      <th style="width: 20%;">البيان</th>
                      <th style="width: 20%;">طريقة الدفع</th>
                      <th >الحساب المصرفي / الخزينة</th>
                      <th style="width: 14%;">قبض</th>
                      <th style="width: 14%;">دفع</th>

                  </tr>
                  </thead>
                  <tbody >
                  @php $sumval=0;$sumexp=0; @endphp
                  @foreach($arr['CustTable'] as $key=>$item)
                      <tr class="font-size-12">
                          <td >{{$item->rec_who->name}}  </td>
                          <td> {{$item->name}}  </td>
                          @if($item->accName)
                              <td> {{$item->accName}}  </td>
                          @else
                              <td> {{$item->kazenaName}}  </td>
                          @endif
                          <td> {{number_format($item->val, 2, '.', ',')}} </td>
                          <td> {{number_format($item->exp, 2, '.', ',')}} </td>



                      </tr>

                      @php $sumval+=$item->val;$sumexp+=$item->exp; @endphp
                  @endforeach
                  <tr class="font-size-12 " style="font-weight: bold">

                      <td style="font-weight:normal;">الإجمــــــــالي  </td>
                      <td>   </td>
                      <td>   </td>
                      <td> {{number_format($sumval, 2, '.', ',')}} </td>
                      <td> {{number_format($sumexp, 2, '.', ',')}} </td>
                  </tr>
                  </tbody>
              </table>
          </div>

  <br>

          <div>
              <label style="font-size: 14pt;margin-right: 12px;" >ترجيع مشتريات</label>
              <table style=" width:100%"   align="right" >
                  <thead style="  margin-top: 8px;">
                  <tr style="background:lightgray">
                      <th style="width: 26%;">التاريخ</th>
                      <th style="width: 16%;">الإجمالي</th>

                  </tr>
                  </thead>
                  <tbody >
                  @php $sumval=0; @endphp
                  @foreach($arr['tar_buy'] as $key=>$item)
                      <tr class="font-size-12">
                          <td> {{$item->tar_date}}  </td>
                          <td> {{number_format($item->sub_tot, 2, '.', ',')}} </td>

                      </tr>

                      @php $sumval+=$item->sub_tot; @endphp
                  @endforeach
                  <tr class="font-size-12 " style="font-weight: bold">
                      <td style="font-weight:normal;">الإجمــــــــالي  </td>
                      <td> {{number_format($sumval, 2, '.', ',')}} </td>


                  </tr>
                  <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
                  <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
                  </tbody>
              </table>
          </div>

  <br>

          <div>
              <label style="font-size: 14pt;margin-right: 12px;" >ترجيع مبيعات</label>
              <table style=" width:100%"   align="right" >
                  <thead style="  margin-top: 8px;">
                  <tr style="background:lightgray">
                      <th style="width: 26%;">التاريخ</th>
                      <th style="width: 16%;">الإجمالي</th>

                  </tr>
                  </thead>
                  <tbody >
                  @php $sumval=0; @endphp
                  @foreach($arr['tar_sell'] as $key=>$item)
                      <tr class="font-size-12">
                          <td> {{$item->tar_date}}  </td>
                          <td> {{number_format($item->sub_tot, 2, '.', ',')}} </td>

                      </tr>

                      @php $sumval+=$item->sub_tot; @endphp
                  @endforeach
                  <tr class="font-size-12 " style="font-weight: bold">
                      <td style="font-weight:normal;">الإجمــــــــالي  </td>
                      <td> {{number_format($sumval, 2, '.', ',')}} </td>


                  </tr>
                  <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
                  <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
                  </tbody>
              </table>
          </div>
  <br>


          <div>   <label style="font-size: 14pt;margin-right: 12px;" >المصروفات</label>
              <table style=" width:100%"   align="right" >
                  <thead style="  margin-top: 8px;">
                  <tr style="background:lightgray">
                      <th style="width: 20%;">البيان</th>
                      <th style="width: 20%;">دفعت من</th>
                      <th style="width: 14%;">المبلغ</th>


                  </tr>
                  </thead>
                  <tbody >
                  @php $sumval=0; @endphp
                  @foreach($arr['masr'] as $key=>$item)
                      <tr class="font-size-12">
                          <td> {{$item->name}}  </td>
                          <td> {{$item->acc_name}} </td>
                          <td> {{number_format($item->val, 2, '.', ',')}} </td>


                      </tr>

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
              </table></div>


  <br>

          <div>
            <label style="font-size: 14pt;margin-right: 12px;" >المرتبات</label>
            <table style=" width:100%"   align="right" >
              <thead style="  margin-top: 8px;">
              <tr style="background:lightgray">
                  <th style="width: 20%;">البيان</th>
                  <th style="width: 14%;">المبلغ</th>

              </tr>
              </thead>
              <tbody >
              @php $sumval=0; @endphp
              @foreach($arr['salary'] as $key=>$item)
                  <tr class="font-size-12">
                      <td> {{$item->tran_type}}  </td>
                      <td> {{number_format($item->val, 2, '.', ',')}} </td>

                  </tr>

                  @php $sumval+=$item->val; @endphp
              @endforeach
              <tr class="font-size-12 " style="font-weight: bold">
                  <td style="font-weight:normal;">الإجمــــــــالي  </td>
                  <td> {{number_format($sumval, 2, '.', ',')}} </td>


              </tr>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
              <td style="border-bottom: none;border-left: none;border-right: none;"> </td>

              </tbody>
          </table>
          </div>

  </div>
@endsection
