@extends('PDF.PrnMasterSpatie')

@section('mainrep')
    <div style="text-align: center">
        <label style="font-size: 14pt;margin-right: 12px;" > تقرير عن المخزون </label>
    </div>
    @if($arr['place'])
        <div >
            <label style="font-size: 14pt;margin-right: 12px;" >    المكان :  </label>
            <label style="font-size: 12pt;font-weight: bolder;">{{$arr['place']}}</label>
        </div>
    @endif
    <div  >
    <table  width="100%"   align="right" >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          @if(!$arr['place'])  <th style="width: 20%;">المكان</th>@endif
          <th >اسم الصتف</th>
          <th style="width: 12%;">رقم الصنف</th>
          <th style="width: 12%;">الرصيد الكلي</th>
          <th style="width: 12%;">رصيد المكان</th>
       @if($arr['show'])
             <th style="width: 12%;">سعر الشراء</th>
             <th style="width: 12%;">تكلفة الشراء</th>
          @endif
      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php $sumtot=0;

      @endphp
      @foreach($res as $key=>$item)
        <tr class="font-size-14">
            @if(!$arr['place'])<td> {{$item->Place->name}}  </td>@endif
            <td>{{$item->Item->name}}</td>
            <td>{{$item->item_id}}</td>
            <td> {{$item->Item->stock1}} </td>
            <td> {{$item->stock1}} </td>
            @if($arr['show'])
              <td> {{number_format($item->price_buy, 2, '.', ',')}} </td>
              <td> {{number_format($item->place_buy_cost, 2, '.', ',')}} </td>
            @endif
        </tr>

        @php $sumtot+=$item->place_buy_cost; @endphp
      @endforeach

      <tr class="font-size-14 " style="font-weight: bold">
          <td style="font-weight:normal;">الإجمــــــــالي  </td>
          @if(!$arr['place'])<td></td>@endif
          <td></td>
          <td></td>
          <td></td>
          @if($arr['show'])
              <td></td>
              <td> {{number_format($sumtot, 2, '.', ',')}} </td>
          @endif
      </tr>
      @if(!$arr['place'])<td style="border-bottom: none;border-left: none;border-right: none;"> </td>@endif
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      <td style="border-bottom: none;border-left: none;border-right: none;"> </td>
      </tbody>
    </table>
  </div>
@endsection
