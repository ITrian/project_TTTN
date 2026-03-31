<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>
<?php require_once APP_ROOT . '/views/layouts/sidebar.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Tra cứu & Bảo hành</h1>

    <!-- Navigation buttons -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-search fa-2x text-primary mb-2"></i><br>
                        Tra cứu theo Serial/Lô hàng
                    </h5>
                    <p class="card-text text-muted mb-3">Tìm kiếm sản phẩm hoặc Serial để kiểm tra trạng thái bảo hành</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <a href="<?php echo BASE_URL; ?>/warranty/listExported" class="text-decoration-none">
                <div class="card shadow" style="cursor: pointer; transition: all 0.3s;">
                    <div class="card-body text-center py-4">
                        <h5 class="card-title mb-3">
                            <i class="fas fa-list fa-2x text-success mb-2"></i><br>
                            Danh sách hàng đã xuất
                        </h5>
                        <p class="card-text text-muted mb-3">Xem toàn bộ sản phẩm đã bán và trạng thái bảo hành</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="" method="GET" class="d-flex gap-2">
                <input type="text" name="keyword" class="form-control form-control-lg" 
                       placeholder="Nhập Serial hoặc Mã hàng" 
                       value="<?php echo htmlspecialchars($data['keyword']); ?>" required>
                <button type="submit" class="btn btn-primary btn-lg">Tra cứu</button>
            </form>
            <?php if ($data['message']) echo "<div class='alert alert-danger mt-3'>{$data['message']}</div>"; ?>
        </div>
    </div>

    <?php if ($data['type'] == 'SINGLE'): $info = $data['result']; ?>
        <div class="card shadow border-left-success">
            <div class="card-header"><h6 class="font-weight-bold text-success">Thông tin bảo hành (Theo Serial)</h6></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Sản phẩm:</strong> <?php echo $info['tenHH']; ?> (<?php echo $info['maHH']; ?>)</p>
                        <p><strong>Serial:</strong> <span class="text-danger fw-bold"><?php echo $info['serial']; ?></span></p>
                        
                        <?php if (isset($info['tinhTrang']) && $info['tinhTrang'] === 'IN_STOCK'): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> Sản phẩm đang ở trong kho (Chưa bán).<br>
                                Chưa kích hoạt thời gian bảo hành.
                            </div>
                        <?php else: ?>
                            <p><strong>Khách hàng:</strong> <?php echo $info['tenKH'] ?? 'Khách lẻ'; ?> (<?php echo $info['sdt'] ?? ''; ?>)</p>
                            <p><strong>Ngày xuất bán:</strong> <?php echo !empty($info['ngayXuat']) ? date('d/m/Y', strtotime($info['ngayXuat'])) : 'N/A'; ?></p>
                            <p><strong>Thời gian BH:</strong> <?php echo $info['thoiGianBaoHanh'] ?? 0; ?> tháng</p>
                            <p><strong>Hạn BH (đến ngày):</strong> 
                                <?php 
                                    if (!empty($info['hanBaoHanh'])) {
                                        $endDate = strtotime($info['hanBaoHanh']);
                                        $isExpired = time() > $endDate;
                                        $dateStr = date('d/m/Y', $endDate);
                                        
                                        if ($isExpired) {
                                            echo "<span class='badge bg-danger fs-6'>$dateStr (Đã quá hạn)</span>";
                                        } else {
                                            echo "<span class='badge bg-success fs-6'>$dateStr (Còn hạn)</span>";
                                        }
                                    } else {
                                        $isExpired = true; // Không xác định ngày -> coi như không cho tạo
                                        echo "N/A";
                                    }
                                ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <?php if (isset($info['tinhTrang']) && $info['tinhTrang'] === 'IN_STOCK'): ?>
                            <button class="btn btn-secondary w-100 fw-bold" disabled>Không thể tạo phiếu (Hàng chưa bán)</button>
                        <?php elseif (isset($isExpired) && $isExpired): ?>
                             <div class="alert alert-danger text-center">
                                 <strong>SẢN PHẨM ĐÃ HẾT HẠN BẢO HÀNH</strong><br>
                                 Không thể tạo phiếu tiếp nhận.
                             </div>
                             <button class="btn btn-secondary w-100 fw-bold" disabled>Đã hết hạn bảo hành</button>
                        <?php else: ?>
                            <form action="<?php echo BASE_URL; ?>/warranty/create" method="POST">
                                <input type="hidden" name="maHH" value="<?php echo $info['maHH']; ?>">
                                <input type="hidden" name="serial" value="<?php echo $info['serial']; ?>">
                                
                                <label>Mô tả lỗi:</label>
                                <textarea name="moTaLoi" class="form-control mb-2" rows="3" required></textarea>
                                <button class="btn btn-warning w-100 fw-bold">Tạo phiếu bảo hành</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php elseif ($data['type'] == 'LIST'): ?>
        <div class="card shadow">
            <div class="card-header bg-info text-white">
                <h6 class="m-0">Chọn Lô hàng để bảo hành:</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Mã Lô</th>
                            <th>Tên Hàng</th>
                            <th>Ngày Nhập</th>
                            <th>Hạn Bảo Hành</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['result'] as $row): ?>
                        <tr>
                            <td><?php echo $row['maLo']; ?></td>
                            <td><?php echo $row['tenHH']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['ngayNhap'])); ?></td>
                            <td>
                                <?php 
                                    $date = date('d/m/Y', strtotime($row['hanBaoHanh']));
                                    echo (strtotime($row['hanBaoHanh']) < time()) ? "<span class='text-danger'>$date (Hết hạn)</span>" : "<span class='text-success'>$date</span>";
                                ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" 
                                        onclick="openWarrantyModal('<?php echo $row['maLo']; ?>', '<?php echo $row['tenHH']; ?>', '<?php echo $row['maHH']; ?>')">
                                    Bảo hành
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="warrantyModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?php echo BASE_URL; ?>/warranty/create" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Bảo hành cho Lô hàng</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>Sản phẩm: <b id="modalProductName"></b></p>
                            
                            <input type="hidden" name="serial" id="modalSerialInput"> <input type="hidden" name="maHH" id="modalMaHHInput">     <div class="mb-3">
                                <label>Mô tả lỗi:</label>
                                <textarea name="moTaLoi" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Lưu phiếu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            function openWarrantyModal(maLo, tenHH, maHH) {
                document.getElementById('modalProductName').innerText = tenHH + ' (' + maLo + ')';
                document.getElementById('modalSerialInput').value = maLo; // Gán mã lô vào serial
                document.getElementById('modalMaHHInput').value = maHH;   // Gán mã hàng
                new bootstrap.Modal(document.getElementById('warrantyModal')).show();
            }
        </script>
    <?php endif; ?>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>