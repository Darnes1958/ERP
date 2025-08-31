

<!doctype html>

<html lang="ar" dir="rtl">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet" />

  <style>

      #content {
          display: table;
      }


      body {
          counter-increment: pageplus1 page;
          counter-reset: pageplus1 1;
          direction: rtl;
          font-family: Amiri ;
      }



      table {
          width: 96%;
          border-collapse: collapse;
          border: 1pt solid  lightgray;

          margin-right: 12px;
          font-size: 12px;
      }

      tr {
          border: 1pt solid  lightgray;
      }
      th {
          border: 1pt solid  lightgray;
      }
      td {
          border: 1pt solid  lightgray;
      }
  </style>
</head>
<body  >
<div  >


        <div class=" display: inline-flex;">
            <div style="text-align: left;position: absolute;left: 0">
                <label style="padding-left: 4px;" > فاتورة مبيعات رقم :  {{$arr['sell']->id}}</label>
                <div >
                    <label style="margin-right: 12px;" >بتاريخ : </label>
                    <label style="font-size: 12px;">{{$arr['sell']->order_date}}</label>

                </div>
            </div>

            <div style="text-align: left;position: absolute;right: 0">
                <label style="font-family: Amiri; font-size: 24pt;" > {{$cus->CompanyName}} </label>
                <div >
                    <label style="font-family: Amiri; font-size: 18pt;">{{$cus->CompanyNameSuffix}}</label>
                </div>
            </div>
        </div>
<div>
    <br>
    <br>
    <br>

</div>

    <div >
        <br>
        <br>
        <br>
        <label style="margin-right: 12px;" >اسم الزبون : </label>
        <label >{{$arr['sell']->Customer->name}}</label>

    </div>
    <div >
        <label style="margin-right: 12px;">صدرت من : </label>
        <label >{{$arr['sell']->Place->name}}</label>

    </div>
    <br>
  <table  width="100%"   align="right" style="border: none;">

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
    @foreach($res as  $item)
      <tr >
          <td style="color: #0c63e4; text-align: center;"> {{ $item->item_id }} </td>
          <td style=" text-align: right;"> {{ $item->Item->name }} </td>
          <td style="text-align: center;"> {{ $item->q1 }} </td>
          <td style=" text-align: right;"> {{ $item->price1 }} </td>
          <td style=" text-align: right;"> {{ $item->sub_tot }}</td>
      </tr>
    @endforeach
    </tbody>
      <tbody>
      <tr style="border-bottom: none;border-right: none;border-left: none;">
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="padding: 4px;border: none;" > إجمالي الفاتورة </td>
          <td style="font-weight: bold;text-align: right;border: white solid 4pt; text-align: center;background: lightgray;">{{$arr['sell']->tot}}</td>
      </tr>
      <tr style="border-bottom: none;border-right: none;border-left: none;">
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="padding: 4px;border: none;" > عمولة مصرفية </td>
          <td style="font-weight: bold;text-align: right;border: white solid 4pt; text-align: center;background: lightgray;">{{$arr['sell']->differ}}</td>
      </tr>
      <tr style="border-bottom: none;border-right: none;border-left: none;">
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="padding: 4px;border: none;" > تكلفة إضاقية </td>
          <td style="font-weight: bold;text-align: right;border: white solid 4pt; text-align: center;background: lightgray;">{{$arr['sell']->cost}}</td>
      </tr>

      <tr style="border: none;">
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="padding: 4px;border: none;">المدفوع </td>
          <td style="font-weight: bold;text-align: right;border: white solid 4pt;text-align: center;background: lightgray;">{{$arr['sell']->pay}}</td>
      </tr >


      <tr style="border: none;">
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="border: none;"></td>
          <td style="padding: 4px;border: none;">المتبقي </td>
          <td style="font-weight: bold;text-align: right;border: white solid 4pt;text-align: center;background: lightgray;">{{$arr['sell']->baky}}</td>
      </tr>

      </tbody>
  </table>

    <br>


</div>



</body>
</html>
