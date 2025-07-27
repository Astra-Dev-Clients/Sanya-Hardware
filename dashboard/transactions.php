<?php
session_start();
if (!isset($_SESSION['store_id'])) {
    header("Location: ../auth/index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transaction History</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Date Range Picker CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


  
<!-- favicons -->
<link rel="icon" type="image/png" href="../assets/img/favicons/favicon-96x96.png" sizes="96x96" />
<link rel="icon" type="image/svg+xml" href="../assets/img/favicons/favicon.svg" />
<link rel="shortcut icon" href="../assets/img/favicons/favicon.ico" />
<link rel="apple-touch-icon" sizes="180x180" href="../assets/img/favicons/apple-touch-icon.png" />
<link rel="manifest" href="../assets/img/favicons/site.webmanifest" />




  <style>
    .details-control {
        cursor: pointer;
    }


     .nav-icon { font-size: 1.8rem; }
    .navbar-brand small { font-size: 0.75rem; font-weight: 500; margin-top: -6px; }
    .nav-link { display: flex; flex-direction: column; align-items: center; padding: 0.5rem 0.75rem; }
    .nav-link span { font-size: 0.7rem; }

  .nav-icon-wrapper {
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    padding: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    margin-right: 8px;
    transition: background-color 0.3s ease;
  }

  .nav-link {
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
  }

  .nav-link:hover .nav-icon-wrapper {
    background-color: #ffffff33;
  }

  .nav-icon {
    font-size: 18px;
    color: white;
  }

  .navbar-nav .nav-item {
    margin-left: 10px;
  }
  </style>
</head>
<body class="bg-light">


<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #143D60;"> 
  <div class="container">
  <a class="navbar-brand d-flex align-items-start" href="#">
    <img src="../assets/img/logos/sanya-bg.png" alt="Logo" style="height: 50px; width: auto;">
  </a>


    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarIcons">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarIcons">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link text-white" href="index.php">
            <span class="nav-icon-wrapper"><i class="bi bi-house nav-icon"></i></span> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="sell.php">
            <span class="nav-icon-wrapper"><i class="bi bi-cart nav-icon"></i></span> Make Sales
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="transactions.php">
            <span class="nav-icon-wrapper"><i class="bi bi-receipt-cutoff nav-icon"></i></span> Transactions
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="analytics.php">
            <span class="nav-icon-wrapper"><i class="bi bi-boxes nav-icon"></i></span> Stock
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="stock_kpis.php">
            <span class="nav-icon-wrapper"><i class="bi bi-bar-chart-line nav-icon"></i></span> Analytics
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="settings.php">
            <span class="nav-icon-wrapper"><i class="bi bi-gear nav-icon"></i></span> Settings
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="../auth/logout.php">
            <span class="nav-icon-wrapper"><i class="bi bi-box-arrow-right nav-icon"></i></span> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>



<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Sales Transactions</h3>
    <input type="text" id="dateRange" class="form-control w-auto" style="min-width: 250px;" placeholder="Filter by date range">
  </div>

  <table id="transactionsTable" class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th><i class="bi bi-eye"></i></th>
        <th>Sale ID</th>
        <th>Assistant</th>
        <th>Payment</th>
        <th>Amount Paid</th>
        <th>Change</th>
        <th>Method</th>
        <th>Transaction ID</th>
        <th>Mpesa Number</th>
        <th>Date</th>
        <th>Action</th>
      </tr>
    </thead>
  </table>
</div>


<!-- jQuery + Bootstrap -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Export Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Date Range Picker -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
  function format(d) {
    let html = `<table class="table table-sm table-bordered mt-2">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Qty</th>
                      <th>Unit Price</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>`;
    d.items.forEach(item => {
      html += `<tr>
                 <td>${item.product_name}</td>
                 <td>${item.quantity}</td>
                 <td>${item.unit_price}</td>
                 <td>${item.total_price}</td>
               </tr>`;
    });
    html += `</tbody></table>`;
    return html;
  }

  let table;

  $(document).ready(function () {
    // Initialize DataTable
    table = $('#transactionsTable').DataTable({
      ajax: {
        url: '../backend/fetch_sales.php',
        dataSrc: 'data'
      },
      columns: [
        {
          className: 'details-control',
          orderable: false,
          data: null,
          defaultContent: '<i class="bi bi-caret-down-square-fill text-primary"></i>',
        },
        { data: 'sale_id' },
        { data: 'assistant_name', defaultContent: '<em>None</em>' },
        { data: 'total_amount' },
        { data: 'amount_paid' },
        { data: 'change_given' },
        { data: 'payment_method' },
        { data: 'transaction_id', defaultContent: '-' },
        { data: 'mpesa_number', defaultContent: '-' },
        { data: 'sale_time' },
        {
            data: 'sale_id',
            orderable: false,
            render: function (data) {
                return `
                <a href="./receipts/view_receipt.php?sale_id=${data}" target="_blank" class="btn btn-sm btn-light border-dark me-1">
                    <i class="bi bi-receipt"></i>
                </a>
                <a href="./receipts/download_receipt.php?sale_id=${data}" class="btn btn-sm btn-warning ">
                    <i class="bi bi-download text-light"></i>
                </a>
                <a href="./receipts/print_receipt.php?sale_id=${data}" target="_blank" class="btn btn-sm btn-info">
                <i class="bi bi-printer text-light"></i>
                </a>
                `;
            }
            }

      ],
      order: [[9, 'desc']],
      dom: 'Bflrtip',
      dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 text-end'B>>" +
           "<'row mb-2'<'col-sm-12'f>>" +
           "<'row'<'col-sm-12'tr>>" +
           "<'row mt-3'<'col-sm-5'i><'col-sm-7'p>>",
      buttons: [
        {
          extend: 'copyHtml5',
          text: '<i class="bi bi-clipboard"></i> Copy',
          className: 'btn btn-sm btn-outline-dark bg-light text-dark rounded me-1'
        },
        {
          extend: 'excelHtml5',
          text: '<i class="bi bi-file-earmark-excel"></i> Excel',
          className: 'btn btn-sm btn-success rounded me-1'
        },
        {
          extend: 'csvHtml5',
          text: '<i class="bi bi-file-earmark-text"></i> CSV',
          className: 'btn btn-sm btn-dark rounded me-1'
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
          className: 'btn btn-sm btn-danger rounded me-1',
          orientation: 'landscape',
          pageSize: 'A4',
          customize: function (doc) {
            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
            doc.content[1].table.headerRows = 1;
            doc.content[1].table.body.forEach(function (row, index) {
              if (index === 0) {
                row.forEach(function (cell) {
                  cell.fillColor = '#143D60';
                  cell.color = 'white';
                });
              }
            });
          }
        },
        {
          extend: 'print',
          text: '<i class="bi bi-printer"></i> Print',
          className: 'btn btn-sm btn-secondary rounded me-1'
        }
      ],
      language: {
        emptyTable: "No products available. Click '+ Add Product' to get started.",
        search: "Search:"
      },
      lengthMenu: [
        [5, 10, 25, 50, 100, -1],
        [5, 10, 25, 50, 100, "All"]
      ],

    });

    // Expand/collapse child row
    $('#transactionsTable tbody').on('click', 'td.details-control', function () {
      const tr = $(this).closest('tr');
      const row = table.row(tr);
      if (row.child.isShown()) {
        row.child.hide();
        tr.removeClass('table-active');
        $(this).html('<i class="bi bi-caret-down-square-fill text-primary"></i>');
      } else {
        row.child(format(row.data())).show();
        tr.addClass('table-active');
        $(this).html('<i class="bi bi-caret-up-square-fill text-danger"></i>');
      }
    });

    // Date Range Picker
    $('#dateRange').daterangepicker({
      autoUpdateInput: false,
      locale: {
        cancelLabel: 'Clear'
      }
    });

    // Filter based on date range
    $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
      $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));

      // Custom filtering
      $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const min = picker.startDate;
        const max = picker.endDate;
        const date = moment(data[9]); // sale_time column
        return date.isBetween(min, max, undefined, '[]');
      });

      table.draw();
    });

    // Clear filter
    $('#dateRange').on('cancel.daterangepicker', function () {
      $(this).val('');
      $.fn.dataTable.ext.search.pop();
      table.draw();
    });

    // Auto refresh every 60 seconds
    setInterval(() => {
      table.ajax.reload(null, false);
    }, 60000);
  });
</script>

</body>
</html>
