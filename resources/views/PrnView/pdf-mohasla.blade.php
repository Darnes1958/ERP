@extends('PrnView.PrnMasterSpatie')

@section('mainrep')
    <div>

        <div style="text-align: center">
            <label style="font-size: 14pt;margin-right: 12px;" >تقرير بالأقساط المحصلة من تاريخ : </label>
            <label style="font-size: 10pt;">{{$arr['Date1']}}</label>
            <label style="font-size: 14pt;margin-right: 12px;" >حتي تاريخ : </label>
            <label style="font-size: 10pt;">{{$arr['Date2']}}</label>
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
                <th style="width: 18%">رقم الحساب</th>
                <th style="width: 12%">اجمالي العقد</th>
                <th style="width: 10%">المسدد</th>
                <th style="width: 12%">الرصيد</th>
                <th style="width: 10%">الخصم</th>
            </tr>
            </thead>
            <tbody id="addRow" class="addRow">

            @php $sumraseed=0;$sumpay=0;$sumsul=0;$sumkst=0; @endphp
            @foreach($res as $key=> $item)
                <tr >
                    <td> {{ $item->Main->Customer->name }} </td>
                    <td style="text-align: center"> {{ $item->Main->id }} </td>
                    <td style="text-align: center"> {{ $item->Main->acc }} </td>
                    <td> {{ number_format($item->Main->sul,2, '.', ',') }} </td>
                    <td> {{ number_format($item->Main->pay,2, '.', ',') }} </td>
                    <td> {{ number_format($item->Main->raseed,2, '.', ',') }} </td>
                    <td> {{ number_format($item->ksm,2, '.', ',') }} </td>


                </tr>
                @php $sumraseed+=$item->Main->raseed;$sumpay+=$item->Main->pay;$sumsul+=$item->Main->sul;$sumkst+=$item->ksm @endphp
            @endforeach
            <tr class="font-size-12 " style="font-weight: bold">
                <td style="font-weight:normal;">الإجمــــــــالي  </td>
                <td> </td>
                <td> </td>
                <td> {{number_format($sumsul, 2, '.', ',')}}  </td>
                <td> {{number_format($sumpay, 2, '.', ',')}}  </td>
                <td> {{number_format($sumraseed, 2, '.', ',')}}  </td>
                <td> {{number_format($sumkst, 2, '.', ',')}}  </td>

            </tr>
            </tbody>
        </table>


    </div>



@endsection

