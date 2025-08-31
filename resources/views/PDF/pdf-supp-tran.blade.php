@extends('PDF.PrnMasterSpatie')

@section('mainrep')
    <div  >

        <div style="text-align: center ; margin-bottom: 5px;">
            <label style="font-size: 14pt;margin-right: 12px;" >كشف حساب المورد : </label>
            <label style="font-size: 10pt;">{{$res->first()->name}}</label>
            <label style="font-size: 14pt;margin-right: 12px;" >من تاريخ : </label>
            <label style="font-size: 10pt;">{{$arr['tran_date']}}</label>


        </div>

        <div style="text-align: center ; margin-bottom: 5px;">
            <label style="font-size: 14pt;margin-right: 12px;" >مدين : </label>
            <label style="font-size: 10pt;">{{$arr['mden']}}</label>
            <label style="font-size: 14pt;margin-right: 12px;" >دائن : </label>
            <label style="font-size: 10pt;">{{$arr['daen']}}</label>
            <label style="font-size: 14pt;margin-right: 12px;" >الرصيد </label>
            <label style="font-size: 10pt;">{{$arr['raseed']}}</label>
        </div>

        <table style="border-collapse:collapse;width: 100%"  >
            <thead >

            <tr style="background:lightgray ;">
                <th style="width: 12%; font-size: 7pt;">البيان</th>
                <th style="width: 12%;font-size: 7pt;">التاريخ</th>
                <th style="width: 10%;font-size: 7pt;">الرقم الألي</th>
                <th style="width: 10%;font-size: 7pt;">طريقة الدفع</th>
                <th style="width: 10%;font-size: 7pt;">مدين</th>
                <th style="width: 10%;font-size: 7pt;">دائن</th>
                <th style="font-size: 7pt;">ملاحظات</th>
            </tr>
            </thead>
            <tbody style="margin-bottom: 40px; ">

            @foreach($res as $key=>$item)

                <tr style="border:1px solid ;">
                    <td style="text-align: center"> {{ $item->rec_who->name }} </td>
                    <td style="text-align: center"> {{ $item->repDate }} </td>
                    <td style="text-align: center"> {{ $item->id }} </td>
                    <td> {{ $item->price_type_name }} </td>
                    <td> {{ $item->mden }} </td>
                    <td> {{ $item->daen }} </td>
                    <td> {{ $item->notes }} </td>
                </tr>


            @endforeach

            <tr  >
                <td>  </td>
                <td>  </td>
                <td>  </td>
                <td>  الإجمالي</td>
                <td style="color: red"> {{ $arr['mden'] }} </td>
                <td style="color: blue"> {{$arr['daen'] }} </td>
                <td>  </td>
            </tr>


            </tbody>
        </table>


    </div>



@endsection



