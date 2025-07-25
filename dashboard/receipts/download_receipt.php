<?php
require '../../vendor/autoload.php';
use Dompdf\Dompdf;

$sale_id = $_GET['sale_id'] ?? 0;

ob_start();
include 'view_receipt.php';  // Reuse the same view
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("receipt_$sale_id.pdf", ["Attachment" => true]);
