<?php
$page_title = 'Add Product';
require_once('includes/load.php');
// Include the PHP QR Code library
include('phpqrcode/qrlib.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Checkin What level user has permission to view this page
page_require_level(3);
$all_categories = find_all('AssetType');
$all_photo = find_all('media');

function generate_asset_id($product_name, $db) {
  // Get the last asset ID from the database
  $sql = "SELECT asset_id FROM assets ORDER BY id DESC LIMIT 1";
  $result = $db->query($sql);
  $last_id = $db->fetch_assoc($result);
  
  if ($last_id) {
    // Extract the numeric part from the last ID and increment it
    $last_numeric_part = intval(substr($last_id['asset_id'], -5));
    $new_numeric_part = str_pad($last_numeric_part + 1, 5, '0', STR_PAD_LEFT);
  } else {
    // If no ID exists, start with 00001
    $new_numeric_part = '00001';
  }
  
  // Construct the new ID
  $prefix = 'UGMC/OPD/';
  $product_prefix = strtoupper(substr($product_name, 0, 2));
  $new_id = $prefix . $product_prefix . '-' . $new_numeric_part;
  
  return $new_id;
}

if(isset($_POST['add_product'])){
  $req_fields = array('name','description', 'financial_value','returns');
  validate_fields($req_fields);
  if(empty($errors)){
    $p_name  = remove_junk($db->escape($_POST['name']));
    $p_desc  = remove_junk($db->escape($_POST['description']));
    $p_financial  = remove_junk($db->escape($_POST['financial_value']));
    $p_return  = remove_junk($db->escape($_POST['returns']));
    $p_status  = remove_junk($db->escape($_POST['status']));
    $p_comments = remove_junk($db->escape($_POST['comments']));
    $p_cat = remove_junk($db->escape($_POST['product-categorie']));

    if (is_null($_POST['product-photo']) || $_POST['product-photo'] === "") {
      $media_id = '0';
    } else {
      $media_id = remove_junk($db->escape($_POST['product-photo']));
    }

    // Generate the asset ID
    $asset_id = generate_asset_id($p_name, $db);

    // Generate the QR Code content
    // For better compatibility, we format the data as a URL
    $qr_content = "Asset ID: {$asset_id}\nName: {$p_name}\nDescription: {$p_desc}\nValue: {$p_financial}\nReturns: {$p_return}";

    // Generate and save the QR Code
    $qr_dir = 'uploads/qrcodes/';
    if (!file_exists($qr_dir)) {
      mkdir($qr_dir, 0777, true);
    }
    $qr_file = $qr_dir . str_replace("/", "-", $asset_id) . '.png';  // Replace "/" with "-" to avoid directory issues

    // Check if the directory is writable
    if (!is_writable($qr_dir)) {
      die('Directory is not writable. Please check the permissions.');
    }

    // Generate the QR code
    QRcode::png($qr_content, $qr_file, 'L', 4, 2);

    // Check if the QR code file was created
    if (!file_exists($qr_file)) {
      die('QR code file was not created. Please check the QR code generation process.');
    }

    // Insert the asset into the database with the QR code path
    $query  = "INSERT INTO assets (";
    $query .=" asset_id, assetname, description, financial_value, returns, status, comments, assettype_id, media_id, qr_code";
    $query .=") VALUES (";
    $query .="'{$asset_id}', '{$p_name}', '{$p_desc}', '{$p_financial}', '{$p_return}', '{$p_status}', '{$p_comments}', '{$p_cat}', '{$media_id}', '{$qr_file}'";
    $query .=")";

    if($db->query($query)){
      $session->msg('s',"Asset added ");
      redirect('add_product.php', false);
    } else {
      $session->msg('d',' Sorry failed to add Asset!');
      redirect('product.php', false);
    }

  } else {
    $session->msg("d", $errors);
    redirect('add_product.php',false);
  }
}
?>









<?php include_once('layouts/header.php'); ?>
<div class="row">
    <div class="col-md-12">
        <?php echo display_msg($msg); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong>
                    <span class="glyphicon glyphicon-th"></span>
                    <span>Add New Asset</span>
                </strong>
            </div>
            <div class="panel-body">
                <div class="col-md-12">
                    <form method="post" action="add_product.php" class="clearfix">

                        <div class="form-group">
                            <div class="row">

                                <div class="form-group col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-th-large"></i>
                                        </span>
                                        <input type="text" class="form-control" name="name" placeholder="Asset Name">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <select class="form-control" name="product-categorie">
                                        <option value="">Select Asset Type</option>
                                        <?php  foreach ($all_categories as $cat): ?>
                                        <option value="<?php echo (int)$cat['id'] ?>">
                                            <?php echo $cat['Type'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select class="form-control" name="product-photo">
                                        <option value="">Select Asset Photo</option>
                                        <?php  foreach ($all_photo as $photo): ?>
                                        <option value="<?php echo (int)$photo['id'] ?>">
                                            <?php echo $photo['file_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-th-large"></i>
                                        </span>
                                        <input type="text" class="form-control" name="description"
                                            placeholder="Description">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-usd"></i>
                                        </span>
                                        <input type="number" class="form-control" name="financial_value"
                                            placeholder="Financial Value">
                                        <span class="input-group-addon">.00</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-th"></i>
                                        </span>
                                        <select class="form-control" name="returns" placeholder="Return">
                                            <option value="">Select...</option>
                                            <option>return</option>
                                            <option>issue</option>
                                        </select>

                                    </div>
                                </div>

                                </div>

                            </div>

                            <div class="form-group">

                            <div class="row">


                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-th"></i>
                                        </span>
                                        <input type="text" class="form-control" name="status" placeholder="Status">

                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="glyphicon glyphicon-th"></i>
                                        </span>
                                        <input type="text" class="form-control" name="comments" placeholder="Comments">

                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_product" class="btn btn-danger">Add Asset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('layouts/footer.php'); ?>