

<!doctype html>

<html lang="ar" dir="rtl">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet" />
  <style>


      body {

          direction: rtl;
          font-family: Amiri ;
          font-size: 16px;
          border: 1px;
          line-height: 32px;
      }




      table {
          width: 96%;
          border-collapse: collapse;
          font-size: 12px;
      }
      tr {
          line-height: 12px;
      }
      th {
          text-align: center;
          border: 1pt solid  gray;
          font-size: 12px;
          height: 30px;
      }


      td {
          text-align: right;
          border: 1pt solid  lightgray;
      }
      .page-break {
          page-break-after: always;
      }
      br[style] {
          display:none;
      }

      #mainlabel  {
          display:inline-block;border-style: dotted;border-top: none;border-right: none;
          border-left: none;padding-left: 4px;padding-right: 4px;text-align: center;
      }
      #mainlabel2  {
          display:inline-block; height: 20px;
      }
  </style>
</head>
<body  >

<div >

  @yield('mainrep')


</div>
</body>
</html>

