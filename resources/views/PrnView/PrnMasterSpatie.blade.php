

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
          border-collapse: collapse;
          font-size: 14px;
      }
      tr {
          line-height: 20px;
      }
      th {
          text-align: center;
          border: 1pt solid  gray;
          font-size: 14px;
          height: 30px;
      }
      caption {
          font-family: Amiri ;

      }
      thead {

          font-family: Amiri ;
      }

      td {
          text-align: right;
          border: 1pt solid  lightgray;
      }




  </style>
</head>
<body  >
<div >


    <div>
      <label style="font-size: 20pt; margin-right: 12px;margin-bottom: 0;margin-top: 0;padding: 0;" >{{$cus->CompanyName}}</label>
    </div>
    <div >
      <label style="font-size: 16pt; margin-right: 12px;margin-bottom: 0;margin-top: 0;padding: 0;">{{$cus->CompanyNameSuffix}}</label>
     </div>
</div>
<br>
<div>

  @yield('mainrep')


</div>
</body>
</html>

