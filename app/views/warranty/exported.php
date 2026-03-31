<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800 mb-0">Danh sách sản phẩm đã xuất</h1>
    </div>

    <!-- Bảng danh sách -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">Sản phẩm đã xuất bán</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Phiếu xuất</th>
                            <th>Ngày xuất</th>
                            <th>Mã hàng</th>
                            <th>Tên sản phẩm</th>
                            <th>Model</th>
                            <th>Số lượng</th>
                            <th>Loại hàng</th>
                            <th>Thời gian BH (tháng)</th>
                            <th>Hạn bảo hành</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['products'])): ?>
                            <?php foreach ($data['products'] as $product): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/warranty/exportDetail/<?php echo $product['maPX']; ?>" class="text-primary">
                                        <?php echo $product['maPX']; ?>
                                    </a>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($product['ngayXuat'])); ?></td>
                                <td><strong><?php echo $product['maHH']; ?></strong></td>
                                <td><?php echo $product['tenHH']; ?></td>
                                <td><?php echo $product['model'] ?? 'N/A'; ?></td>
                                <td><?php echo $product['soLuong']; ?></td>
                                <td>
                                    <?php 
                                        if ($product['loaiHang'] == 'SERIAL') {
                                            echo '<span class="badge bg-info">Serial</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Lô</span>';
                                        }
                                    ?>
                                </td>
                                <td><?php echo $product['thoiGianBaoHanh'] ?? 0; ?></td>
                                <td>
                                    <?php if ($product['hanBaoHanh']): ?>
                                        <?php echo $product['hanBaoHanh_formatted']; ?>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['conBaoHanh']): ?>
                                        <span class="badge bg-success">Còn hạn</span>
                                        <br>
                                        <small class="text-success">Còn <?php echo $product['ngayConLai']; ?> ngày</small>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Hết hạn</span>
                                        <br>
                                        <small class="text-danger">Quá <?php echo $product['ngayQuaHan'] ?? 0; ?> ngày</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['conBaoHanh']): ?>
                                        <form action="<?php echo BASE_URL; ?>/warranty/create" method="POST" style="display:inline;">
                                            <input type="hidden" name="maHH" value="<?php echo $product['maHH']; ?>">
                                            <input type="hidden" name="serial" value="<?php echo $product['maPX']; ?>">
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#warrantyModal" 
                                                onclick="setWarrantyData('<?php echo $product['maHH']; ?>', '<?php echo $product['tenHH']; ?>', '<?php echo $product['maPX']; ?>')">
                                                Tạo phiếu
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Hết hạn</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">Chưa có sản phẩm nào được xuất</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal tạo phiếu bảo hành -->
<div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>/warranty/create" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="warrantyModalLabel">Tạo phiếu bảo hành</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Sản phẩm:</strong></label>
                        <p id="modalProductName" class="form-control-plaintext border-bottom pb-2"></p>
                    </div>
                    <input type="hidden" name="maHH" id="modalMaHH">
                    <input type="hidden" name="serial" id="modalSerial">
                    
                    <div class="mb-3">
                        <label for="moTaLoi" class="form-label">Mô tả lỗi <span class="text-danger">*</span></label>
                        <textarea name="moTaLoi" id="moTaLoi" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Lưu phiếu bảo hành</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function setWarrantyData(maHH, tenHH, serial) {
        document.getElementById('modalMaHH').value = maHH;
        document.getElementById('modalSerial').value = serial;
        document.getElementById('modalProductName').textContent = tenHH + ' (Mã: ' + maHH + ')';
        document.getElementById('moTaLoi').value = ''; // Clear previous input
    }
</script>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>
