<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "<form method='post' enctype='multipart/form-data'>
        <input type='file' name='test_file'>
        <button type='submit'>Test Upload</button>
      </form>";

if ($_FILES['test_file']) {
    $result = uploadFile($_FILES['test_file'], 'profile');
    echo "<pre>".print_r($result, true)."</pre>";
}