<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการใช้ห้องเรียน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">รายงานการใช้ห้องเรียน</h1>
        <form action="report.php" method="post" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="roomImages" class="form-label">อัพโหลดรูปภาพห้อง</label>
                <input class="form-control" type="file" id="roomImages" name="roomImages[]" accept="image/*" multiple onchange="previewImages(event)" required>
            </div>
            <div class="mb-3" id="imagePreviewContainer" style="display: flex; flex-wrap: wrap; gap: 10px;">
                <!-- รูปภาพที่เลือกจะแสดงที่นี่ -->
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="light" name="light">
                <label class="form-check-label" for="light">ปิดไฟ</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="aircon" name="aircon">
                <label class="form-check-label" for="aircon">ปิดแอร์</label>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="fan" name="fan">
                <label class="form-check-label" for="fan">ปิดพัดลม</label>
            </div>
            <button type="submit" class="btn btn-primary">ส่งรายงาน</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImages(event) {
            var files = event.target.files;
            var container = document.getElementById('imagePreviewContainer');
            container.innerHTML = ''; // Clear the previous images

            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var reader = new FileReader();

                reader.onload = (function(file) {
                    return function(e) {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '150px';
                        img.style.maxHeight = '150px';
                        img.style.margin = '10px';
                        container.appendChild(img);
                    };
                })(file);

                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
