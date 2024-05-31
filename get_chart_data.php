<?php
require_once('includes/load.php');

// Function to get chart data
function get_chart_data() {
  global $db;
  $sql = "SELECT assettype.Type AS label, COUNT(assets.id) AS value
          FROM assets
          JOIN assettype ON assets.assettype_id = assettype.id
          GROUP BY assettype.Type";
  $result = $db->query($sql);
  $data = [];
  while ($row = $db->fetch_assoc($result)) {
    $data[] = $row;
  }
  return $data;
}

header('Content-Type: application/json');
$chart_data = get_chart_data();
$labels = array_column($chart_data, 'label');
$values = array_column($chart_data, 'value');

echo json_encode(['labels' => $labels, 'values' => $values]);
?>
