<?php
function sendLineNotify($message, $token, $images = []) {
    $url = 'https://notify-api.line.me/api/notify';
    $headers = [
        'Content-Type: multipart/form-data',
        'Authorization: Bearer ' . $token
    ];
    $data = [
        'message' => $message
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    foreach ($images as $imagePath) {
        $data['imageFile'] = curl_file_create($imagePath);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    }

    curl_close($ch);

    return [$httpcode, $result];
}

$token = 'JLNp4h5kHDX0HmJEcpTIsOWWIIQT8MgGMjRkucFHB5N';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $light = isset($_POST['light']) ? 'ปิด' : 'เปิด';
    $aircon = isset($_POST['aircon']) ? 'ปิด' : 'เปิด';
    $fan = isset($_POST['fan']) ? 'ปิด' : 'เปิด';

    $message = "รายงานการใช้ห้องเรียน:\n- ไฟ: $light\n- แอร์: $aircon\n- พัดลม: $fan";

    $uploadDir = 'uploads/';
    $imagePaths = [];

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['roomImages']['tmp_name'] as $key => $tmp_name) {
        $uploadFile = $uploadDir . basename($_FILES['roomImages']['name'][$key]);
        if (move_uploaded_file($tmp_name, $uploadFile)) {
            $imagePaths[] = $uploadFile;
        } else {
            echo 'Failed to upload image: ' . $_FILES['roomImages']['name'][$key] . "\n";
            error_log('Failed to upload image: ' . $_FILES['roomImages']['error'][$key]);
        }
    }

    if (!empty($imagePaths)) {
        list($status_code, $response) = sendLineNotify($message, $token, $imagePaths);
        echo 'Status Code: ' . $status_code . "\n";
        echo 'Response: ' . $response . "\n";
    } else {
        echo 'No images were uploaded.';
    }
}
?>
