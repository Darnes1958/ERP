@extends('PDF.PrnMaster')

@section('mainrep')
  <div  >
      <div style="text-align: center;font-size: 16pt">تقرير بالمخزون </div>
      <br>
    <table style="border-collapse:collapse;"  >
      <thead>
      <tr style="background:lightgray">
        <th style="width: 14%">تكلفة البيع</th>
        <th style="width: 14%">سعر البيع</th>
        <th style="width: 14%">تكلفة الشراء</th>
        <th style="width: 14%">سعر الشراء</th>
        <th style="width: 14%">رصيد المكان</th>
        <th style="width: 14%">الرصيد الكلي</th>
        <th style="width: 14%">الوحدة</th>
        <th >اسم الصنف</th>
        <th style="width: 14%">رقم الصنف</th>
        <th style="width: 14%">المكان</th>
      </tr>
      </thead>
      <tbody style="margin-bottom: 40px; ">
      @foreach($res as $key => $item)
        <tr class="font-size-12">
          <td> {{ $item->sell_cost }} </td>
          <td> {{ $item->Item->price1 }} </td>
          <td> {{ $item->buy_cost }} </td>
          <td> {{ $item->Item->price_buy }} </td>
          <td> {{ $item->stock1 }} </td>
          <td> {{ $item->Item->stock1 }} </td>
          <td> {{ $item->Item->Unita->name }} </td>
          <td> {{ $item->Item->name }} </td>
          <td> {{ $item->item_id }} </td>
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

