

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
      #footer {
          height: 30px;
          position: fixed;

          margin: 5px;
          bottom: 0;
          text-align: center;
      }
      #footer .page:after {
          content:   counter(page);
      }
      #footer .pageplus1:after {
          content:  counter(pageplus1);
      }

      @page {
          size: 21cm 29.7cm;
          margin: 4px;
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


        <label style="font-family: Amiri; font-size: 24pt; margin-right: 12px;" >{{$cus->CompanyName}}</label>
    <br>
        <label style="font-family: Amiri; font-size: 18pt;margin-right: 12px;">{{$cus->CompanyNameSuffix}}</label>

    <br>
    <br>
    <br>
    <label style="margin-right: 12px;"> فاتورة رقم :  {{$res->id}}</label>
    <div >
        <label style="font-size: 12px;">{{$res->order_date}}</label>
        <label style="margin-right: 12px;" >بتاريخ : </label>
    </div>
    <div >
        <label >{{$res->Supplier->name}}</label>
        <label style="margin-right: 12px;" >اسم المورد : </label>
    </div>
    <div >
        <label >{{$res->Place->name}}</label>
        <label style="margin-right: 12px;">صدرت من : </label>
    </div>
    <br>
  <table  width="100%"   align="right" style="border: none;">

    <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
    <tr  style="background: #9dc1d3;" >
        <th width="12%">المجموع</th>
        <th width="12%">السعر </th>
        <th width="8%">الكمية</th>
        <th>اسم الصنف </th>
        <th  width="12%">رقم الصنف</th>
    </tr>
    </thead>
    <tbody style="margin-bottom: 40px; ">
    @foreach($orderdetail as  $item)
      <tr >
          <td style=" text-align: right;"> {{ $item->sub_input }}</td>
          <td style=" text-align: right;"> {{ $item->price_input }} </td>
          <td style="text-align: center;"> {{ $item->q1 }} </td>
          <td style=" text-align: right;"> {{ $item->Item->name }} </td>
          <td style="color: #0c63e4; text-align: center;"> {{ $item->item_id }} </td>
      </tr>
      <div id="footer" style="height: 50px; width: 100%; margin-bottom: 0px; margin-top: 10px;
                              display: flex;  justify-content: center;">
          <label class="page"></label>
          <label> صفحة رقم </label>
      </div>
    @endforeach
    </tbody>
      <tbody>
      <tr style="border-bottom: none;border-right: none;border-left: none;">
        <td style="font-weight: bold;text-align: right;border: white solid 4pt; text-align: center;background: lightgray;">{{$res->tot}}</td>
        <td style="padding: 4px;border: none;" > إجمالي الفاتورة </td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>

      </tr>
      <tr style="border: none;">
        <td style="font-weight: bold;text-align: right;border: white solid 4pt;text-align: center;background: lightgray;">{{$res->pay}}</td>
        <td style="padding: 4px;border: none;">المدفوع </td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>
      </tr >


      <tr style="border: none;">
        <td style="font-weight: bold;text-align: right;border: white solid 4pt;text-align: center;background: lightgray;">{{$res->baky}}</td>
        <td style="padding: 4px;border: none;">المتبقي </td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>
        <td style="border: none;"></td>
      </tr>

      </tbody>
  </table>

    <br>


</div>



</body>
</html>
