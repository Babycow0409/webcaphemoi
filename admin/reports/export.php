<?php
session_start();

if (!isset($_SESSION["admin"])) {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../includes/db_connect.php';
require_once __DIR__ . '/../../classes/Report.php';

$reportType = isset($_GET['type']) ? $_GET['type'] : 'revenue';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] . ' 00:00:00' : null;
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] . ' 23:59:59' : null;

$report = new Report($conn);
$report->exportToCSV($dateFrom, $dateTo, $reportType);

