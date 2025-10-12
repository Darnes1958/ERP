@extends('PrnView.PrnMasterSpatie')

@section('mainrep')
    <div>

        <div style="text-align: center">
            <label style="font-size: 14pt;margin-right: 12px;" >تقرير بالعقود المتاخرة السداد حتي تاريخ : </label>
            <label style="font-size: 10pt;">{{$arr['RepDate']}}</label>


        </div>
        <div >
            <label style="font-size: 14pt;margin-right: 12px;" >للمصرف التجميعي : </label>
            <label style="font-size: 10pt;">{{$arr['BankName']}}</label>

        </div>
        <table style=" margin-left: 2%;margin-right: 2%; margin-bottom: 4%; margin-top: 2%;">
            <thead style="  margin-top: 8px;">
            <tr style="background: #9dc1d3;">
                <th>اسم الزبون</th>
                <th style="width: 10%">رقم العقد</th>
                <th style="width: 16%">رقم الحساب</th>
                <th style="width: 12%">اجمالي العقد</th>
                <th style="width: 10%">المسدد</th>
                <th style="width: 8%">المتاخرة</th>
                <th style="width: 14%">ت.اخر قسط</th>
            </tr>
            </thead>
            <tbody id="addRow" class="addRow">

            @php $sumpay=0;$sumsul=0; @endphp

            @foreach($res as $key=> $item)
                <tr >
                    <td> {{ $item->Customer->name }} </td>
                    <td style="text-align: center"> {{ $item->id }} </td>
                    <td> {{ $item->acc }} </td>
                    <td> {{ number_format($item->sul,2, '.', ',') }} </td>
                    <td> {{ number_format($item->pay,2, '.', ',') }} </td>
                    <td style="text-align: center"> {{ $item->Late }} </td>
                    <td style="text-align: center"> {{ $item->LastKsm }} </td>
                </tr>
                @php $sumpay+=$item->pay;$sumsul+=$item->sul; @endphp
            @endforeach
            <tr class="font-size-12 " style="font-weight: bold">
                <td style="font-weight:normal;">الإجمــــــــالي  </td>
                <td> </td>
                <td> </td>
                <td> {{number_format($sumsul, 2, '.', ',')}}  </td>
                <td> {{number_format($sumpay, 2, '.', ',')}}  </td>
                <td></td>
                <td></td>

            </tr>
            </tbody>
        </table>


    </div>



@endsection

