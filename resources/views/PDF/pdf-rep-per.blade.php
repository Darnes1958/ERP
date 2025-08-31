@extends('PDF.PrnMasterSpatie')

@section('mainrep')



        <div style="text-align: center">
            <div>
                <label style="font-size: 14pt;margin-right: 12px;" > تقرير عن إذن صرف مخازن رقم  </label>
                <label style="font-size: 10pt;">{{$arr['per']->id}}</label>
            </div>
        </div>
            <div>
                <label style="font-size: 14pt;margin-right: 12px;" > بتاريخ  </label>
                <label style="font-size: 10pt;">{{$arr['per']->per_date}}</label>

            </div>

            <div>
                <label style="font-size: 14pt;margin-right: 12px;" >    من : </label>
                <label style="font-size: 10pt;">{{$arr['per']->Placefrom->name}}</label>
            </div>
        <div>
            <label style="font-size: 14pt;margin-right: 12px;" >    إلي : </label>
            <label style="font-size: 10pt;">{{$arr['per']->PlaceTo->name}}</label>
        </div>



    <div >
    <table  width="100%"   align="right" >
      <thead style="  margin-top: 8px;">
      <tr style="background:lightgray">
          <th style="width: 20%;">رقم الصنف</th>
          <th >اسم الصنف</th>
          <th style="width: 20%;">الكمية</th>

      </tr>
      </thead>
      <tbody id="addRow" class="addRow">
      @php ;$sumtot=0;$sumcash=0;$sumnot_cash=0;

      @endphp
      @foreach($res as $key=>$item)
        <tr class="font-size-14">
            <td>{{$item->item_id}}</td>
            <td> {{$item->Item->name}}  </td>
            <td>{{$item->quantity}}</td>

        </tr>
      @endforeach




      </tbody>
    </table>




  </div>



@endsection
