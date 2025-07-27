

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
          width: 60%;

          font-size: 14px;
      }
      tr {
          line-height: 14px;
      }
      th {
          text-align: center;

          font-size: 14px;
          height: 30px;
      }


      td {
          text-align: right;

      }


  </style>
</head>
<body  >

<div >




            @foreach($res as  $item)

        <table  width="60%"   align="right" >     <thead style=" font-family: DejaVu Sans, sans-serif; margin-top: 8px;" >
            <tr  style="background: lightgray;" >
                <th  width="40%">الصنف</th>
                <th width="10%">السعر</th>

            </tr>
            </thead>
            <tbody >
                <tr >
                    <td style=" text-align: right;"> {{ $item->name }} </td>
                    <td style=" text-align: center;"> {{ $item->price1 }} </td>

                </tr>
                <br>
             <tbody>

        </table>
                <br> <br> <br>
    @endforeach
</div>
</body>
</html>

