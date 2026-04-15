

<!doctype html>

<html lang="ar" dir="rtl">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet" />

    <style>

        body {
            direction: rtl;
            font-family: Amiri ;
        }

        table {
            width: 96%;
            font-size: 14px;
            border-collapse: collapse;
        }
        tr {
            line-height: 20px;
            border: 1pt solid  gray;
        }
        th {
            text-align: center;
            font-size: 14px;
            height: 30px;
            border: 1pt solid  gray;
        }
        td {
            text-align: right;
            border: 1pt solid  gray;
        }
    </style>
</head>
<body>
<div>
    <label style="font-family: Amiri; font-size: 24pt; margin-right: 12px;" >{{$cus->CompanyName}}</label>
    <br>
    <label style="font-family: Amiri; font-size: 18pt;margin-right: 12px;">{{$cus->CompanyNameSuffix}}</label>
    <br>
    <br>
    <br>
    <label style="margin-right: 12px;"> فاتورة رقم :  {{$res->id}}</label>
    <div >
        <label style="margin-right: 12px;" >بتاريخ : </label>
        <label style="font-size: 12px;">{{$res->order_date}}</label>
    </div>
    <div >
        <label style="margin-right: 12px;" >اسم المورد : </label>
        <label >{{$res->Supplier->name}}</label>
    </div>
    <div >
        <label style="margin-right: 12px;">صدرت من : </label>
        <label >{{$res->Place->name}}</label>

    </div>
    <br>

  <table>
    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >
        <th  width="12%">رقم الصنف</th>
        <th>اسم الصنف </th>
        <th width="8%">الكمية</th>
        <th width="12%">السعر </th>
        <th width="12%">المجموع</th>
    </tr>
    </thead>
    <tbody style="margin-bottom: 40px; ">
    @foreach($arr['orderdetail'] as  $item)
      <tr >
          <td style="color: #0c63e4; text-align: center;"> {{ $item->item_id }} </td>
          <td style=" text-align: right;"> {{ $item->Item->name }} </td>
          <td style="text-align: center;">  {{ number_format($item->q1,0) }} </td>
          <td style=" text-align: right;"> {{ $item->price_input }} </td>
          <td style=" text-align: right;"> {{ $item->sub_input }}</td>
      </tr>
    @endforeach
    </tbody>
      <tbody>
      <tr style="border: none">
        <td style="border: none"></td>
        <td style="border: none"></td>
        <td style="border: none"></td>
          <td  > إجمالي الفاتورة </td>
          <td style="font-weight: bold;text-align: right; background: lightgray;">{{$res->tot}}</td>
      </tr>
      <tr style="border: none">


        <td style="border: none"></td>
        <td style="border: none"></td>
        <td style="border: none"></td>
          <td style="padding: 4px;">المدفوع </td>
          <td style="font-weight: bold; text-align: right;background: lightgray;">{{$res->pay}}</td>
      </tr >


      <tr style="border: none">
        <td style="border: none"></td>
        <td style="border: none"></td>
        <td style="border: none"></td>
          <td style="padding: 4px;">المتبقي </td>
          <td style="font-weight: bold;text-align: right;background: lightgray;">{{$res->baky}}</td>
      </tr>

      </tbody>
  </table>

    <br>


</div>



</body>
</html>
