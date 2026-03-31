<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Chi tiết bảo hành phiếu xuất</h1>

    <!-- Thông tin phiếu xuất -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">Thông tin phiếu xuất</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Mã phiếu xuất:</strong> <span class="text-primary"><?php echo $data['exportInfo']['maPX']; ?></span></p>
                    <p><strong>Ngày xuất:</strong> <?php echo date('d/m/Y H:i', strtotime($data['exportInfo']['ngayXuat'])); ?></p>
                    <p><strong>Khách hàng:</strong> <?php echo $data['exportInfo']['tenKH'] ?? 'Khách lẻ'; ?></p>
                    <p><strong>Điện thoại:</strong> <?php echo $data['exportInfo']['sdt'] ?? 'N/A'; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Địa chỉ:</strong> <?php echo $data['exportInfo']['diaChi'] ?? 'N/A'; ?></p>
                    <p><strong>Người xuất:</strong> <?php echo $data['exportInfo']['tenND'] ?? 'N/A'; ?></p>
                    <p><strong>Ghi chú:</strong> <?php echo $data['exportInfo']['ghiChu'] ?? 'N/A'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách sản phẩm trong phiếu xuất -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold">Sản phẩm & Trạng thái bảo hành</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Mã hàng</th>
                            <th>Tên sản phẩm</th>
                            <th>Model</th>
                            <th>Số lượng</th>
                            <th>Loại hàng</th>
                            <th>Thời gian BH (tháng)</th>
                            <th>Hạn bảo hành</th>
                            <th>Khoảng thời gian</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data['products'])): ?>
                            <?php foreach ($data['products'] as $product): ?>
                            <tr>
                                <td><strong><?php echo $product['maHH']; ?></strong></td>
                                <td><?php echo $product['tenHH']; ?></td>
                                <td><?php echo $product['model'] ?? 'N/A'; ?></td>
                                <td class="text-center"><?php echo $product['soLuong']; ?></td>
                                <td>
                                    <?php 
                                        if ($product['loaiHang'] == 'SERIAL') {
                                            echo '<span class="badge bg-info">Serial</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Lô</span>';
                                        }
                                    ?>
                                </td>
                                <td class="text-center"><?php echo $product['thoiGianBaoHanh'] ?? 0; ?></td>
                                <td>
                                    <?php if ($product['hanBaoHanh']): ?>
                                        <strong><?php echo $product['hanBaoHanh_formatted']; ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['conBaoHanh']): ?>
                                        <span class="badge bg-success">Còn hạn</span>
                                        <br>
                                        <small class="text-success fw-bold">
                                            Còn <?php echo $product['ngayConLai']; ?> ngày 
                                            (<?php echo $product['soThang']; ?> tháng)
                                        </small>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Hết hạn</span>
                                        <br>
                                        <small class="text-danger fw-bold">Quá <?php echo $product['ngayQuaHan'] ?? 0; ?> ngày</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($product['conBaoHanh']): ?>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#warrantyModal"
                                                onclick="setWarrantyData('<?php echo $product['maHH']; ?>', '<?php echo addslashes($product['tenHH']); ?>', '<?php echo $product['maPX']; ?>')">
                                                <i class="fas fa-plus-circle"></i> Phiếu BH
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>Hết hạn</button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <!-- Hiển thị danh sách Serial nếu là sản phẩm loại SERIAL -->
                            <?php if ($product['loaiHang'] == 'SERIAL' && !empty($product['serials'])): ?>
                            <tr class="table-light">
                                <td colspan="10">
                                    <strong>Danh sách Serial:</strong><br>
                                    <div class="mt-2">
                                        <?php foreach ($product['serials'] as $serial): ?>
                                            <span class="badge bg-primary me-2 mb-2" style="font-size: 12px; padding: 6px 10px;">
                                                <?php echo $serial['serial']; ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php elseif ($product['loaiHang'] == 'SERIAL' && empty($product['serials'])): ?>
                            <tr class="table-light">
                                <td colspan="10">
                                    <small class="text-muted"><i class="fas fa-info-circle"></i> Chưa cập nhật serial cho sản phẩm này</small>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Không có sản phẩm trong phiếu xuất này</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Nút quay lại -->
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/warranty/listExported" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>
</div>

<!-- Modal tạo phiếu bảo hành -->
<div class="modal fade" id="warrantyModal" tabindex="-1" aria-labelledby="warrantyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>/warranty/create" method="POST">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="warrantyModalLabel">Tạo phiếu bảo hành</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Sản phẩm:</strong></label>
                        <p id="modalProductName" class="form-control-plaintext border-bottom pb-2 fw-bold text-primary"></p>
                    </div>
                    <input type="hidden" name="maHH" id="modalMaHH">
                    <input type="hidden" name="serial" id="modalSerial">
                    
                    <div class="mb-3">
                        <label for="moTaLoi" class="form-label">Mô tả lỗi <span class="text-danger">*</span></label>
                        <textarea name="moTaLoi" id="moTaLoi" class="form-control" rows="4" placeholder="Nhập chi tiết lỗi của sản phẩm..." required></textarea>
                        <small class="text-muted">Vui lòng mô tả chi tiết về lỗi/vấn đề cần bảo hành</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning fw-bold">Lưu phiếu bảo hành</button>
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
