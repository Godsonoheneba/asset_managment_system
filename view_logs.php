<?php
$page_title = 'View Logs';
require_once('includes/load.php');
// Checkin What level user has permission to view this page
page_require_level(1);

$all_logs = find_all_logs();
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Logs</span>
       </strong>
      
      </div>
      <div class="panel-body">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th>User ID</th>
              <th>Username</th>
              <th>Action</th>
              <th>Timestamp</th>
              <!-- <th class="text-center" style="width: 100px;">Actions</th> -->
            </tr>
          </thead>
          <tbody>
            <?php foreach ($all_logs as $log): ?>
              <tr>
                <td class="text-center"><?php echo count_id(); ?></td>
                <td><?php echo remove_junk($log['user_id']); ?></td>
                <td><?php echo remove_junk($log['username']); ?></td>
                <td><?php echo remove_junk($log['action']); ?></td>
                <td><?php echo remove_junk($log['timestamp']); ?></td>
                <!-- <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_log.php?id=<?php echo (int)$log['id'];?>" class="btn btn-info btn-xs"  title="Edit" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-edit"></span>
                    </a>
                    <a href="delete_log.php?id=<?php echo (int)$log['id'];?>" class="btn btn-danger btn-xs"  title="Delete" data-toggle="tooltip">
                      <span class="glyphicon glyphicon-trash"></span>
                    </a>
                  </div>
                </td> -->
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>
