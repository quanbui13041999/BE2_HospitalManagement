<!DOCTYPE html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lưu Trữ & Tra Cứu Tài Liệu Y Khoa Cá Nhân</title>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/document.css') }}">
</head>

<body>
  @if(session('success'))
  <div class="alert alert-success" id="flash-msg">
    <span>✅</span> {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-error" id="flash-msg">
    <span>❌</span> {{ session('error') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
  @endif
  @if(session('warning'))
  <div class="alert alert-error" id="flash-msg">
    <span>⚠</span> {{ session('warning') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
  </div>
  @endif

  <!-- HEADER -->
  <div class="header">
    <div>
      <div class="header-title">Lưu Trữ &amp; Tra Cứu Tài Liệu Y Khoa Cá Nhân</div>
      <div class="header-breadcrumb">
        <!-- Thêm link trang chủ ở đây -->
        <a href="{{ url('/') }}" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 4px;">
          <span>🏠</span> Trang chủ
        </a>
        <span>›</span>
        <span style="color: var(--accent); font-weight: 600;">Medical Document</span>
        <span>›</span>
        Kho lưu trữ số tài liệu y tế và phân loại 
      </div>
    </div>
  </div>

  <!-- MAIN -->
  <div class="main">

    <!-- LEFT: Upload + Docs -->
    <div>

      <!-- UPLOAD CARD -->
      <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="documents_snapshot" value="{{ $documentsSnapshot }}">
        <div class="upload-card">
          <div class="upload-zone" id="dropZone"
            ondragover="event.preventDefault(); this.classList.add('drag-over')"
            ondragleave="this.classList.remove('drag-over')"
            ondrop="handleDrop(event)">

            <div class="upload-icon">📤</div>
            <div class="upload-label">Tải lên tài liệu y tế</div>
            <div class="upload-hint">Hỗ trợ: JPG, PNG, PDF · Tối đa 20MB / tệp</div>
            <button type="button" class="btn-upload" onclick="document.getElementById('fileInput').click()">📁 Chọn tệp để tải lên</button>
            <input type="file" id="fileInput" name="file" accept=".jpg,.jpeg,.png,.pdf" hidden onchange="updateFilePreview()">
          </div>

          <div id="filePreview" class="file-preview hidden"></div>

          <div class="section-label">Phân loại tài liệu</div>
          <div class="tag-group" id="upload-category-group">
            @foreach(App\Models\MedicalDocument::categories() as $key => $cat)
            <button type="button"
              class="tag {{ request('category', 'xet_nghiem') === $key ? 'active-blue' : 'gray' }}"
              data-value="{{ $key }}"
              onclick="selectTag(this, 'upload-category-input')">
              {!! $cat['icon'] !!} {{ $cat['label'] }}
            </button>
            @endforeach
          </div>
          <input type="hidden" name="category" id="upload-category-input" value="{{ request('category', 'xet_nghiem') }}">
          @error('category') <p class="field-error">{{ $message }}</p> @enderror

          <div class="btn-view-wrap" style="gap: 10px; flex-wrap: wrap; margin-top: 16px;">
            <button type="button" class="btn-upload" onclick="document.getElementById('fileInput').click()">📁 Chọn tệp</button>
            <button type="submit" class="btn-upload">💾 Tải lên</button>
          </div>
          @error('file') <p class="field-error">{{ $message }}</p> @enderror
        </div>
      </form>

      <!-- DOCS -->
      <div class="doc-section">
        <div class="doc-section-header">📁 Tài liệu của bạn</div>
        <div class="docs-grid">
          @forelse($documents as $document)
          @php
          $previewClass = match($document->doc_type) {
          'hinh_anh' => 'green-soft',
          'don_thuoc' => 'pink-soft',
          'chuyen_vien' => 'blue-soft',
          default => 'blue-soft',
          };
          $categoryInfo = $document->categoryInfo;
          @endphp
          <div class="doc-card">
            <div class="doc-preview {{ $previewClass }}">{{ $categoryInfo['icon'] }}</div>
            <div class="doc-body">
              <div class="doc-name">{{ $document->doc_name }}</div>
              <div class="doc-meta">{{ $categoryInfo['label'] }} · {{ optional($document->uploaded_at)->format('d/m/Y') }}</div>
              <div class="doc-footer">
                <span class="doc-size">{{ $document->formatted_size }}</span>
                <span class="badge {{ $categoryInfo['badge'] }}">{{ $categoryInfo['icon'] }} {{ $categoryInfo['label'] }}</span>
              </div>
              <div class="btn-view-wrap" style="flex-wrap: wrap; gap: 6px;">
                <a href="{{ route('documents.show', $document) }}" target="_blank" class="btn-view">👁 Xem</a>
                <a href="{{ route('documents.download', $document) }}" class="btn-action" title="Tải xuống">⬇️</a>
                @if((int) $document->user_id === (int) auth()->user()->user_id)
                <a href="{{ route('documents.edit', $document) }}" class="btn-action edit" title="Chỉnh sửa">✏️</a>
                <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xoá tài liệu này?')" style="display:inline-flex;">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="document_snapshot" value="{{ hash_hmac('sha256', implode('|', [
                    $document->doc_id,
                    $document->user_id,
                    $document->record_id,
                    $document->doc_type,
                    $document->doc_name,
                    $document->file_path,
                    optional($document->uploaded_at)->format('Y-m-d H:i:s'),
                  ]), (string) config('app.key')) }}">
                  <button type="submit" class="btn-action" title="Xoá">🗑</button>
                </form>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div class="empty-state" style="grid-column: 1 / -1;">
            <div class="empty-icon">📭</div>
            <p>Chưa có tài liệu nào trong kho lưu trữ.</p>
          </div>
          @endforelse
        </div><!-- /docs-grid -->

        @if($documents->hasPages())
        <div class="pagination">
          <span class="pagination-info">Hiển thị {{ $documents->count() }} / {{ $documents->total() }} tài liệu</span>
          <div class="pagination-btns">
            @if($documents->onFirstPage())
            <span class="btn-page" aria-disabled="true">← Trước</span>
            @else
            <a href="{{ $documents->previousPageUrl() }}" class="btn-page">← Trước</a>
            @endif
            @if($documents->hasMorePages())
            <a href="{{ $documents->nextPageUrl() }}" class="btn-page primary">Tiếp →</a>
            @else
            <span class="btn-page primary" aria-disabled="true">Tiếp →</span>
            @endif
          </div>
        </div>
        @endif
      </div>
    </div><!-- /left -->

    <!-- SIDEBAR -->
    <div class="sidebar">

      <!-- SEARCH & FILTER -->
      <div class="sidebar-card">
        <div class="sidebar-card-title">🔍 Bộ lọc &amp; Tìm kiếm</div>
        <form action="{{ route('documents.index') }}" method="GET">
          <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input class="search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên tệp, bệnh viện, nội dung...">
          </div>
          <div class="filter-row">
            <select class="filter-select" name="category">
              <option value="">Tất cả loại</option>
              @foreach(App\Models\MedicalDocument::categories() as $key => $cat)
              <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $cat['label'] }}</option>
              @endforeach
            </select>
            <select class="filter-select" name="period">
              <option value="">Tất cả thời gian</option>
              <option value="this_month" {{ request('period') === 'this_month' ? 'selected' : '' }}>Tháng này</option>
              <option value="last_3_months" {{ request('period') === 'last_3_months' ? 'selected' : '' }}>3 tháng gần đây</option>
              <option value="this_year" {{ request('period') === 'this_year' ? 'selected' : '' }}>Năm nay</option>
            </select>
          </div>
          <button type="submit" class="btn-upload" style="width: 100%; margin-top: 12px;">Áp dụng</button>
        </form>
      </div>

      <!-- STATS -->
      <div class="sidebar-card">
        <div class="sidebar-card-title">📊 Tổng quan kho lưu trữ</div>
        <div class="stats-grid">
          <div class="stat-box">
            <div class="stat-icon amber">📁</div>
            <div>
              <div class="stat-value">{{ $stats['total'] }}</div>
              <div class="stat-label">Tổng tệp</div>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon purple">💾</div>
            <div>
              <div class="stat-value">{{ $stats['total_size'] }}</div>
              <div class="stat-label">Dung lượng</div>
            </div>
          </div>
          @foreach($stats['categoryCounts'] as $categoryKey => $categoryCount)
          @php
            $categoryInfo = App\Models\MedicalDocument::categories()[$categoryKey];
            $colorClass = match($categoryKey) {
              'xet_nghiem' => 'blue',
              'hinh_anh' => 'green',
              'don_thuoc' => 'purple',
              'chuyen_vien' => 'amber',
              default => 'blue',
            };
          @endphp
          <div class="stat-box">
            <div class="stat-icon {{ $colorClass }}">{!! $categoryInfo['icon'] !!}</div>
            <div>
              <div class="stat-value">{{ $categoryCount }}</div>
              <div class="stat-label">{{ $categoryInfo['label'] }}</div>
            </div>
          </div>
          @endforeach
        </div>
      </div>

    </div><!-- /sidebar -->

  </div><!-- /main -->


  <script>
    document.addEventListener('DOMContentLoaded', function() {

      const dropZone = document.getElementById('dropZone');
      const fileInput = document.getElementById('fileInput');
      const preview = document.getElementById('filePreview');
      const submitBtn = document.querySelector('button[type="submit"]');

      // disable submit ban đầu
      submitBtn.disabled = true;

      // CLICK ZONE
      dropZone.addEventListener('click', () => fileInput.click());

      // DRAG OVER
      dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
      });

      // DRAG LEAVE
      dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
      });

      // DROP FILE
      dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');

        const files = e.dataTransfer.files;

        if (files.length > 0) {
          const dt = new DataTransfer();
          dt.items.add(files[0]);
          fileInput.files = dt.files;

          updatePreview(files[0]);
        }
      });

      // CHỌN FILE
      fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
          updatePreview(fileInput.files[0]);
        }
      });

      window.selectTag = function(el, hiddenInputId = 'upload-category-input') {
        document.querySelectorAll('#upload-category-group .tag').forEach(tag => {
          tag.classList.remove('active-blue');
          tag.classList.add('gray');
        });

        el.classList.remove('gray');
        el.classList.add('active-blue');
        document.getElementById(hiddenInputId).value = el.dataset.value;
      };

      window.clearPreview = function() {
        fileInput.value = '';
        preview.classList.add('hidden');
        preview.innerHTML = '';
        submitBtn.disabled = true;
      };

      function updatePreview(file) {
        preview.innerHTML = '';

        const text = document.createElement('span');
        text.textContent = `Đã chọn: ${file.name} (${Math.round(file.size / 1024)} KB)`;

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'preview-remove';
        removeButton.title = 'Xóa file đã chọn';
        removeButton.textContent = '×';
        removeButton.addEventListener('click', window.clearPreview);

        preview.appendChild(text);
        preview.appendChild(removeButton);
        preview.classList.remove('hidden');

        submitBtn.disabled = false;
      }

    });
  </script>


</body>

</html>
