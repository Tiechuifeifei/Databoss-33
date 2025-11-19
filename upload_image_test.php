<?php
$itemId = $_GET["itemId"];

?>
<?php
echo "<h1>VERSION TEST 123</h1>";
$itemId = $_GET["itemId"];
?>

<!DOCTYPE html>
<html>
<body>

<h2>Upload up to 3 Images for Item #<?php echo $itemId; ?></h2>

<form action="upload_image.php" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="itemId" value="<?php echo $itemId; ?>">

    <label>Select up to 3 images:</label><br>
    <input type="file" name="itemImages[]" accept="image/*" multiple required>
    <br><br>

    <button type="submit">Upload Images</button>

</form>

</body>
</html>
