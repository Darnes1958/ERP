@extends('PDF.PrnMasterSpatie')

@section('mainrep')
  <div  >
      <div style="text-align: center;font-size: 16pt">تقرير بالمخزون </div>
      <br>
    <table style="border-collapse:collapse;"  >
      <thead>
      <tr style="background:lightgray">
          <th style="width: 10%">المكان</th>
          <th style="width: 7%">رقم الصنف</th>
          <th >اسم الصنف</th>
          <th style="width: 8%">الوحدة</th>
          <th style="width: 7%">الرصيد الكلي</th>
          <th style="width: 7%">رصيد المكان</th>
          <th style="width: 7%">سعر الشراء</th>
          <th style="width: 7%">تكلفة الشراء</th>
          <th style="width: 7%">سعر البيع</th>
          <th style="width: 7%">تكلفة البيع</th>
      </tr>
      </thead>
      <tbody style="margin-bottom: 40px; ">
      @foreach($res as $key => $item)
        <tr class="font-size-12">
            <td> {{ $item->Place->name }} </td>
            <td> {{ $item->Place->name }} </td>
            <td style="text-align: center"> {{ $item->item_id }} </td>
            <td> {{ $item->Item->name }} </td>
            <td style="text-align: center"> {{ $item->Item->Unita->name }} </td>
            <td> {{ $item->Item->stock1 }} </td>
            <td> {{ $item->stock1 }} </td>
            <td> {{  number_format($item->Item->price_buy,2, '.', ',') }} </td>
            <td> {{  number_format($item->buy_cost,2, '.', ',') }} </td>
            <td> {{  number_format($item->Item->price1,2, '.', ',') }} </td>
          <td> {{  number_format($item->sell_cost,2, '.', ',') }} </td>


        </tr>


      @endforeach
      </tbody>
    </table>

  </div>
@endsection

