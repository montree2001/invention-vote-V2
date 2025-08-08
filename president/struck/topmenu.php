<header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link sidebartoggler nav-icon-hover" id="headerCollapse" href="javascript:void(0)">
                                <i class="ti ti-menu-2"></i>
                            </a>
                        
                        </li>
                       
                    </ul>
                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                       

               
                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                    
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-icon-hover" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
                                   <i class="ti ti-settings"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                                    <div class="message-body">
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item" data-bs-toggle="modal" data-bs-target="#edit_profile">
                                            <i class="ti ti-user fs-6"></i>
                                            <p class="mb-0 fs-3">บัญชีผู้ใช้งาน</p>
                                        </a>
                                        <a href="javascript:void(0)" class="d-flex align-items-center gap-2 dropdown-item" data-bs-toggle="modal" data-bs-target="#edit_password">
                                            <i class="ti ti-key fs-6"></i>
                                            <p class="mb-0 fs-3">เปลี่ยนรหัสผ่าน</p>
                                        </a>
                                        <a href="../process/logout.php" class="btn btn-outline-primary mx-3 mt-2 d-block">ออกจากระบบ</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
      <!-- modal แก้ไขรหัสผ่าน-->
      <div class="modal fade" id="edit_password" tabindex="-1" aria-labelledby="edit_passwordLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary">
                                    <h5 class="modal-title text-white" id="edit_passwordLabel">แก้ไขรหัสผ่าน</h5>
                                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body bg-light">
                                    <form action="../process/edit_password_pre.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                        <div class="mb-3">
                                            <label for="old_password" class="form-label">รหัสผ่านเดิม</label>
                                            <input type="password" class="form-control" id="old_password" name="old_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            <button type="submit" class="btn btn-primary">บันทึก</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- แก้ไขข้อมูลส่วนตัว มีชื่อ และ Username -->
                    <div class="modal fade " id="edit_profile" tabindex="-1" aria-labelledby="edit_profileLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary">
                                    <h5 class="modal-title text-white" id="edit_profileLabel">บัญชีผู้ใช้งาน</h5>
                                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body bg-light">
                                    <form action="../process/edit_username_pre.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $_SESSION['username']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">ชื่อ</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo $_SESSION['name']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="name" class="form-label">ตำแหน่ง</label>
                                            <input type="text" class="form-control" id="rank" name="rank" value="<?php echo  $_SESSION['president_rank'] ?>" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                                            <button type="submit" class="btn btn-primary">บันทึก</button>
                                        </div>
                                        <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>