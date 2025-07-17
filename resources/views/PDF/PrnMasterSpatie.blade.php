

<!doctype html>

<html lang="ar" dir="rtl">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <link href="https://fonts.googleapis.com/css2?family=Amiri&display=swap" rel="stylesheet" />
  <style>

      #content {
          align-items: center;
          display: inline-flex;
      }
      #towlabel {

          display: inline;
      }
      .float-container {
          border: 3px solid #fff;
          padding: 20px;
      }

      .float-child {

          float: right;
          padding: 2px;

      }
      float-child2 {
          width: 60%;
          float: left;
          padding: 2px;

      }
      body {
          counter-increment: pageplus1 page;
          counter-reset: pageplus1 1;
          direction: rtl;
          font-family: Amiri ;
      }

      #header {
          position: fixed;
          top: -115px;
          width: 100%;
          height: 109px;

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
      .page-break {
          page-break-after: always;
      }
      br[style] {
          display:none;
      }
      .page-break {
          page-break-after: always;
      }

  </style>
</head>
<body  >
<div class="header">
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

