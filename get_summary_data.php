<?php
require_once('includes/load.php');

// Function to get summary data
function get_summary_data() {
  global $db;
  $sql = "SELECT COUNT(id) AS totalAssets, SUM(financial_value) AS totalValue FROM assets";
  $result = $db->query($sql);
  $summary = $db->fetch_assoc($result);

  $status_sql = "SELECT status, COUNT(id) AS count FROM assets GROUP BY status";
  $status_result = $db->query($status_sql);
  $status_data = [];
  while ($row = $db->fetch_assoc($status_result)) {
    $status_data[] = $row;
  }

  return ['summary' => $summary, 'statusData' => $status_data];
}

header('Content-Type: application/json');
$data = get_summary_data();

$statusLabels = array_column($data['statusData'], 'status');
$statusValues = array_column($data['statusData'], 'count');

echo json_encode([
  'totalAssets' => $data['summary']['totalAssets'],
  'totalValue' => $data['summary']['totalValue'],
  'statusLabels' => $statusLabels,
  'statusValues' => $statusValues
]);
?>
