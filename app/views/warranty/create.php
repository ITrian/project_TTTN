<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4">Tiếp nhận Bảo hành</h3>

    <div class="card mb-4">
        <div class="card-header bg-info text-white">Bước 1: Kiểm tra Serial</div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-auto">
                    <input type="text" name="search_serial" class="form-control" placeholder="Nhập số Serial..." value="<?= $data['search_serial'] ?>" required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Kiểm tra</button>
                </div>
            </form>

            <?php if(!empty($data['error'])): ?>
                <div class="alert alert-danger mt-3"><?= $data['error'] ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($data['info'])): 
        $info = $data['info'];
        $homNay = date('Y-m-d');
        $hetHan = ($info['hanBaoHanh'] && $info['hanBaoHanh'] < $homNay);
    ?>
    <div class="card shadow">
        <div class="card-header bg-success text-white">Bước 2: Thông tin sản phẩm & Tạo phiếu</div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Thông tin sản phẩm</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Sản phẩm:</strong> <?= $info['tenHH'] ?></li>
                        <li class="list-group-item"><strong>Serial:</strong> <?= $info['serial'] ?></li>
                        <li class="list-group-item">
                            <strong>Hạn bảo hành (Lô):</strong> 
                            <?= $info['hanBaoHanh'] ? date('d/m/Y', strtotime($info['hanBaoHanh'])) : 'Không xác định' ?>
                            <?php if($hetHan): ?>
                                <span class="badge bg-danger">ĐÃ HẾT HẠN</span>
                            <?php else: ?>
                                <span class="badge bg-success">CÒN HẠN</span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5>Lịch sử mua hàng</h5>
                    <ul class="list-group">
                        <li class="list-group-item"><strong>Khách hàng:</strong> <?= $info['tenKH'] ?? 'Chưa xuất bán/Khách lẻ' ?></li>
                        <li class="list-group-item"><strong>SĐT:</strong> <?= $info['sdtKH'] ?? 'N/A' ?></li>
                        <li class="list-group-item"><strong>Ngày xuất kho:</strong> <?= $info['ngayXuat'] ? date('d/m/Y', strtotime($info['ngayXuat'])) : 'Chưa xuất' ?></li>
                    </ul>
                </div>
            </div>
            
            <hr>

            <form action="<?= BASE_URL ?>/warranty/store" method="POST">
                <input type="hidden" name="serial" value="<?= $info['serial'] ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả lỗi từ khách hàng (*)</label>
                    <textarea name="moTaLoi" class="form-control" rows="3" required placeholder="Ví dụ: Mở không lên nguồn, vỡ màn hình..."></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <?php if($hetHan): ?>
                        <div class="alert alert-warning me-3 mb-0 p-2">
                            <i class="bi bi-exclamation-triangle"></i> Sản phẩm đã hết hạn bảo hành theo lô. Cân nhắc tính phí.
                        </div>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary btn-lg">Tạo phiếu tiếp nhận</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>