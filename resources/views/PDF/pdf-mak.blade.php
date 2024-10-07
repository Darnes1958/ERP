@extends('PDF.PrnMaster2')

@section('mainrep')
  <div  >
      <div style="text-align: center;font-size: 16pt">تقرير بالمخزون </div>
      <br>
    <table style="border-collapse:collapse;"  >
      <thead>
      <tr style="background:lightgray">
        <th style="width: 7%">تكلفة البيع</th>
        <th style="width: 7%">سعر البيع</th>
        <th style="width: 7%">تكلفة الشراء</th>
        <th style="width: 7%">سعر الشراء</th>
        <th style="width: 7%">رصيد المكان</th>
        <th style="width: 7%">الرصيد الكلي</th>
        <th style="width: 8%">الوحدة</th>
        <th >اسم الصنف</th>
        <th style="width: 7%">رقم الصنف</th>
        <th style="width: 10%">المكان</th>
      </tr>
      </thead>
      <tbody style="margin-bottom: 40px; ">
      @foreach($res as $key => $item)
        <tr class="font-size-12">
          <td> {{  number_format($item->sell_cost,2, '.', ',') }} </td>
          <td> {{  number_format($item->Item->price1,2, '.', ',') }} </td>
          <td> {{  number_format($item->buy_cost,2, '.', ',') }} </td>
          <td> {{  number_format($item->Item->price_buy,2, '.', ',') }} </td>
          <td> {{ $item->stock1 }} </td>
          <td> {{ $item->Item->stock1 }} </td>
          <td style="text-align: center"> {{ $item->Item->Unita->name }} </td>
          <td> {{ $item->Item->name }} </td>
          <td style="text-align: center"> {{ $item->item_id }} </td>
          <td> {{ $item->Place->name }} </td>
        </tr>
        <div id="footer" style="height: 50px; width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">

            <label class="page"></label>
            <label> صفحة رقم </label>
        </div>

      @endforeach
      </tbody>
    </table>

  </div>
@endsection

