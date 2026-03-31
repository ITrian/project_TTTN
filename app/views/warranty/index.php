<?php require_once APP_ROOT . '/views/layouts/header.php'; ?>
<?php require_once APP_ROOT . '/views/layouts/sidebar.php'; ?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Bảo hành</h1>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="warrantyTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="search-tab" data-bs-toggle="tab" data-bs-target="#search-tab-pane" type="button" role="tab">
                <i class="fas fa-search"></i> Tra cứu bảo hành
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-tab-pane" type="button" role="tab">
                <i class="fas fa-list"></i> Danh sách hàng đã xuất
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="report-tab" data-bs-toggle="tab" data-bs-target="#report-tab-pane" type="button" role="tab">
                <i class="fas fa-chart-bar"></i> Báo cáo bảo hành
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="warrantyTabContent">
        <!-- Tab 1: Tra cứu bảo hành -->
        <div class="tab-pane fade show active" id="search-tab-pane" role="tabpanel">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-search"></i> Tra cứu theo Serial hoặc Mã hàng</h5>
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
                                                $isExpired = true;
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
                                    
                                    <input type="hidden" name="serial" id="modalSerialInput">
                                    <input type="hidden" name="maHH" id="modalMaHHInput">
                                    
                                    <div class="mb-3">
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
                        document.getElementById('modalSerialInput').value = maLo;
                        document.getElementById('modalMaHHInput').value = maHH;
                        new bootstrap.Modal(document.getElementById('warrantyModal')).show();
                    }
                </script>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Danh sách hàng đã xuất -->
        <div class="tab-pane fade" id="list-tab-pane" role="tabpanel">
            <iframe src="<?php echo BASE_URL; ?>/warranty/listExported" style="width: 100%; height: 800px; border: none;"></iframe>
        </div>

        <!-- Tab 3: Báo cáo bảo hành -->
        <div class="tab-pane fade" id="report-tab-pane" role="tabpanel">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="m-0"><i class="fas fa-chart-bar"></i> Báo cáo bảo hành</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-left-success h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Tổng sản phẩm đã xuất</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800" id="totalProd">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-info h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Còn bảo hành</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800" id="activeWar">-</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-left-danger h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đã hết hạn</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800" id="expiredWar">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Top sản phẩm gần hết hạn bảo hành</h6>
                        </div>
                        <div class="card-body">
                            <div id="reportTable">
                                <p class="text-muted text-center">Đang tải dữ liệu...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                // Lấy dữ liệu báo cáo bằng AJAX
                fetch('<?php echo BASE_URL; ?>/warranty/getReportData')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('totalProd').innerText = data.total || 0;
                        document.getElementById('activeWar').innerText = data.active || 0;
                        document.getElementById('expiredWar').innerText = data.expired || 0;

                        if (data.topProducts && data.topProducts.length > 0) {
                            let html = '<table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Sản phẩm</th><th>Khách hàng</th><th>Còn lại</th><th>Hạn BH</th></tr></thead><tbody>';
                            data.topProducts.forEach(item => {
                                html += '<tr><td>' + item.tenHH + '</td><td>' + (item.tenKH || 'Khách lẻ') + '</td><td><span class="badge bg-warning">' + item.ngayConLai + ' ngày</span></td><td>' + item.hanBaoHanh_formatted + '</td></tr>';
                            });
                            html += '</tbody></table>';
                            document.getElementById('reportTable').innerHTML = html;
                        }
                    })
                    .catch(err => {
                        console.error('Lỗi tải báo cáo:', err);
                        document.getElementById('reportTable').innerHTML = '<p class="text-danger text-center">Lỗi khi tải dữ liệu</p>';
                    });
            </script>
        </div>
    </div>
</div>

<?php require_once APP_ROOT . '/views/layouts/footer.php'; ?>