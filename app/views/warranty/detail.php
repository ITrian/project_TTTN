<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> In Phiếu
        </button>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow border-top-primary" style="border-top: 5px solid #4e73df;">
                <div class="card-body p-5">
                    
                    <div class="text-center mb-5">
                        <h2 class="fw-bold text-uppercase">Phiếu Tiếp Nhận Bảo Hành</h2>
                        <p class="text-muted">Mã phiếu: <strong><?php echo $data['ticket']['maBH']; ?></strong></p>
                        <p>Ngày nhận: <?php echo date('d/m/Y H:i', strtotime($data['ticket']['ngayNhan'])); ?></p>
                    </div>

                    <table class="table table-bordered">
                        <tr>
                            <th width="30%" class="bg-light">Sản phẩm</th>
                            <td class="fs-5 fw-bold"><?php echo $data['ticket']['tenHH'] ?? 'N/A'; ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Số Serial / Mã Lô</th>
                            <td class="text-danger font-monospace"><?php echo $data['ticket']['serial']; ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Mô tả lỗi (Khách báo)</th>
                            <td><?php echo nl2br($data['ticket']['moTaLoi']); ?></td>
                        </tr>
                        <tr>
                            <th class="bg-light">Trạng thái</th>
                            <td>
                                <?php if($data['ticket']['trangThai'] == 0) echo '<span class="badge bg-warning text-dark">Mới tiếp nhận</span>'; ?>
                                <?php if($data['ticket']['trangThai'] == 1) echo '<span class="badge bg-primary">Đang xử lý</span>'; ?>
                                <?php if($data['ticket']['trangThai'] == 2) echo '<span class="badge bg-success">Hoàn thành</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-light">Nhân viên tiếp nhận</th>
                            <td><?php echo $data['ticket']['tenND']; ?></td>
                        </tr>
                    </table>

                    <div class="row mt-5 text-center">
                        <div class="col-6">
                            <strong>Khách hàng</strong><br>
                            <small>(Ký, xác nhận)</small>
                        </div>
                        <div class="col-6">
                            <strong>Nhân viên kỹ thuật</strong><br>
                            <small>(Ký, ghi rõ họ tên)</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * { visibility: hidden; }
        .card, .card * { visibility: visible; }
        .card { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
        .btn, .sidebar, header { display: none !important; }
    }
</style>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>