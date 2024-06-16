<?php
  $page_title = 'Home Page';
  require_once('includes/load.php');
  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false); }

  check_username_password_match();
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
  <div class="row">
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-heading">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>Summary</span>
          </strong>
        </div>
        <div class="panel-body">
          <h3 id="totalAssets">Total Assets: 0</h3>
          <h3 id="totalValue">Total Value: $0</h3>
        </div>
      </div>
    </div>
    <div class="col-md-8">
      <canvas id="assetsChart"></canvas>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <!-- <canvas id="statusChart"></canvas> -->
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  fetch('get_chart_data.php')
    .then(response => response.json())
    .then(data => {
      const ctx = document.getElementById('assetsChart').getContext('2d');
      const chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Total Assets',
            data: data.values,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    })
    .catch(error => console.error('Error fetching chart data:', error));

  fetch('get_summary_data.php')
    .then(response => response.json())
    .then(data => {
      document.getElementById('totalAssets').innerText = 'Total Assets: ' + data.totalAssets;
      document.getElementById('totalValue').innerText = 'Total Value: $' + data.totalValue.toLocaleString();
      
      const statusCtx = document.getElementById('statusChart').getContext('2d');
      const statusChart = new Chart(statusCtx, {
        type: 'pie',
        data: {
          labels: data.statusLabels,
          datasets: [{
            label: 'Asset Status',
            data: data.statusValues,
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
            borderColor: ['#FF6384', '#36A2EB', '#FFCE56'],
            borderWidth: 1
          }]
        }
      });
    })
    .catch(error => console.error('Error fetching summary data:', error));
});
</script>
