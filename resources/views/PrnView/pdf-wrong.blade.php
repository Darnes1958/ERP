@extends('PrnView.PrnMasterSpatie')

@section('mainrep')
    <div>

        <div style="text-align: center">
            <label style="font-size: 14pt;margin-right: 12px;" >تقرير بالأقساط الواردة بالخطأ حتي تاريخ : </label>
            <label style="font-size: 10pt;">{{$arr['date']}}</label>

        </div>
        <div >
            <label style="font-size: 14pt;margin-right: 12px;" >للمصرف التجميعي : </label>
            <label style="font-size: 10pt;">{{$arr['TajName']}}</label>
        </div>
        <table>
            <thead >
            <tr style="background: #9dc1d3;">
                <th>اسم الزبون</th>
                <th style="width: 18%">رقم الحساب</th>
                <th style="width: 12%">التاريخ</th>
                <th style="width: 10%">القسط</th>

            </tr>
            </thead>
            <tbody >


            @foreach($res as $key=> $item)
                <tr >
                    <td> {{ $item->name }} </td>
                    <td> {{ $item->acc }} </td>
                    <td style="text-align: center"> {{ $item->wrong_date }} </td>
                    <td> {{ number_format($item->kst,2, '.', ',') }} </td>
                </tr>

            @endforeach

            </tbody>
        </table>


    </div>



@endsection

