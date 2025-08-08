<?php
session_start();

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ระบบประมวลผลสิ่งประดิษฐ์คนรุ่นใหม่ พัฒนาโดยวิทยาลัยการอาชีพปราสาท</title>
  <meta name="description" content="ระบบประมวลผลสิ่งประดิษฐ์คนรุ่นใหม่ พัฒนาโดยวิทยาลัยการอาชีพปราสาท">
  <meta name="keywords" content="ระบบประมวลผล, สิ่งประดิษฐ์คนรุ่นใหม่, วิทยาลัยการอาชีพปราสาท">
  <meta property="og:image" content="https://ivt.prasat.ac.th/img/logo.png">
  <meta property="og:image:width" content="100"> <!-- ความกว้างของภาพ -->
  <meta property="og:image:height" content="100"> <!-- ความสูงของภาพ -->
  <meta property="og:title" content="ระบบประมวลผลสิ่งประดิษฐ์คนรุ่นใหม่ พัฒนาโดยวิทยาลัยการอาชีพปราสาท">
  <link rel="shortcut icon" type="image/png" href="img/logo.png" />


  <?php include 'struck/head.php'; ?>
  <style>
    @font-face {
      font-family: 'Kanit';
      src: url('font/Kanit-Regular.ttf') format('truetype');
    }

    body {
      font-family: 'Kanit', sans-serif;
    }
  </style>
</head>

<body style="background: url(&quot;img/innovation.jpg&quot;); background-size: cover;background-attachment: fixed;">


  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="./index.html" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="img/logo.png" width="100px" alt="">
                </a>
                <p class="text-center" style="font-size:18px;">ระบบประมวลผลสิ่งประดิษฐ์คนรุ่นใหม่</p>

                <form action="process/login.php" method="POST">
                  <div class="mb-3">
                    <label for="username" class="form-label">ชื่อผู้ใช้งาน</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                  </div>
                  <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">รหัสผ่าน</label>
                    <input type="password" class="form-control" name="password" required>
                  </div>


                  <button type="submit" class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">เข้าสู่ระบบ</button>


                </form>

                <!--  สร้างฟอร์ม login -->


      
                  <p class="text-center mb-0">
                    <i class="ti ti-copyright"></i> พัฒนาระบบโดยวิทยาลัยการอาชีพปราสาท</p>
            
                <!-- แสดงเวอร์ชั่น -->







              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>







  <?php include 'struck/script.php'; ?>


  <?php
  // Include this function in your PHP file

  // Check if there's an alert in the session
  if (isset($_SESSION['alert_type']) && isset($_SESSION['alert_message']) && isset($_SESSION['alert_title'])) {
    // Display the alert using SweetAlert2
    echo "
        <script>
            Swal.fire({
                icon: '{$_SESSION['alert_type']}',
                title: '{$_SESSION['alert_title']}',
                text: '{$_SESSION['alert_message']}',
            });
        </script>
    ";
    // Clear the session variables to avoid displaying the same alert multiple times
    unset($_SESSION['alert_type']);
    unset($_SESSION['alert_message']);
    unset($_SESSION['alert_title']);
  }
  ?>


</body>

</html>