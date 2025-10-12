

<!doctype html>

<html lang="ar" dir="rtl">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <script src="https://cdn.tailwindcss.com"></script>
  <style>
      @media print {
          .pagebreak { page-break-before: always; } /* page-break-after works, as well */
      }
      html {
          -webkit-print-color-adjust: exact;
      }
      @page {
          size:  21cm 29.7cm ;
          margin: 4px;
      }
      #pageborder {
          position:fixed;
          left: 0;
          right: 0;
          top: 40px;
          bottom: 40px;
          border: 2px solid ;color: #bf800c;
      }
      .order-td {
          border-left: none;
          border-top: none;
          border-right: none;
          font-size: 12pt;
          text-align: right;
      }
      #content {
          align-items: center;
          display: inline-flex;
      }
      #towlabel {

          display: inline;
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



      table {
          width: 96%;

          border-collapse: collapse;
          font-size: 14px;
      }
      tr {
          line-height: 18px;
      }
      th {
          text-align: center;
          border: 1pt solid  gray;
          font-size: 12px;
          height: 30px;
      }
      caption {
          font-family: DejaVu Sans, sans-serif ;

      }
      thead {

          font-family: DejaVu Sans, sans-serif;
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

       footer {
           font-size: 12px;
       }
      header {
          font-size: 12px;
      }

  </style>
</head>
<body  >

<br>
<br>
         <div>
          <label style="font-size: 20pt; margin-right: 12px;margin-bottom: 0;margin-top: 0;padding: 0;" >{{$cus->CompanyName}}</label>
         </div>
         <div >
          <label style="font-size: 16pt; margin-right: 12px;margin-bottom: 0;margin-top: 0;padding: 0;">{{$cus->CompanyNameSuffix}}</label>
         </div>
<br>
<br>

    <div >

      @yield('mainrep')


    </div>
</body>
</html>

