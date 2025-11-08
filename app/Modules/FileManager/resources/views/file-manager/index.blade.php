<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Manager</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .root-path {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .breadcrumbs {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            align-items: center;
            font-size: 14px;
        }
        
        .breadcrumb-item {
            color: #3498db;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .breadcrumb-item:hover {
            background-color: #ecf0f1;
        }
        
        .breadcrumb-item.active {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .breadcrumb-separator {
            color: #95a5a6;
        }
        
        .statistics {
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 30px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .stat-value {
            color: #2c3e50;
            font-weight: 600;
            font-size: 16px;
        }
        
        .content {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .error-message {
            background: #e74c3c;
            color: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        
        th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
        
        tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s;
        }
        
        tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        td {
            padding: 12px 20px;
        }
        
        .item-icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        .item-name {
            color: #2c3e50;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
        }
        
        .item-name:hover {
            color: #3498db;
        }
        
        .item-type-directory .item-name {
            font-weight: 500;
        }
        
        .item-size {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .item-modified {
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .file-preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .file-preview-modal.active {
            display: flex;
        }
        
        .file-preview-content {
            background: #fff;
            border-radius: 8px;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            display: flex;
            flex-direction: column;
        }
        
        .file-preview-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .file-preview-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .file-preview-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #7f8c8d;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        
        .file-preview-close:hover {
            background: #ecf0f1;
        }
        
        .file-preview-body {
            padding: 20px;
            overflow: auto;
        }
        
        .file-preview-code {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .file-info {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .file-info-item {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .file-info-label {
            font-weight: 600;
            color: #495057;
            display: inline-block;
            min-width: 100px;
        }
        
        .file-info-value {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>📁 File Manager</h1>
            <div class="root-path">Root: {{ $rootPath }}</div>
            
            <div class="breadcrumbs">
                @foreach($breadcrumbs as $index => $crumb)
                    @if($index > 0)
                        <span class="breadcrumb-separator">/</span>
                    @endif
                    @if($loop->last)
                        <span class="breadcrumb-item active">{{ $crumb['name'] }}</span>
                    @else
                        <a href="?path={{ $crumb['path'] }}" class="breadcrumb-item">
                            {{ $crumb['name'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </header>
        
        @if(isset($error))
            <div class="error-message">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif
        
        <div class="statistics">
            <div class="stat-item">
                <span class="stat-label">Directories:</span>
                <span class="stat-value">{{ $statistics['directories'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Files:</span>
                <span class="stat-value">{{ $statistics['files'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Total Size:</span>
                <span class="stat-value">{{ number_format($statistics['total_size'] / 1024, 2) }} KB</span>
            </div>
        </div>
        
        <div class="content">
            @if(count($items) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Modified</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr class="item-type-{{ $item['type'] }}">
                                <td>
                                    @if($item['type'] === 'directory')
                                        <a href="?path={{ $item['path'] }}" class="item-name">
                                            <span class="item-icon">📁</span>
                                            {{ $item['name'] }}
                                        </a>
                                    @else
                                        <a href="#" class="item-name file-link" data-path="{{ $item['path'] }}">
                                            <span class="item-icon">📄</span>
                                            {{ $item['name'] }}
                                        </a>
                                    @endif
                                </td>
                                <td class="item-size">
                                    @if($item['size'] !== null)
                                        {{ number_format($item['size'] / 1024, 2) }} KB
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="item-modified">
                                    {{ date('Y-m-d H:i:s', $item['modified']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">📂</div>
                    <p>This directory is empty</p>
                </div>
            @endif
        </div>
    </div>
    
    <div class="file-preview-modal" id="filePreviewModal">
        <div class="file-preview-content">
            <div class="file-preview-header">
                <div class="file-preview-title" id="previewTitle">File Preview</div>
                <button class="file-preview-close" onclick="closePreview()">&times;</button>
            </div>
            <div class="file-preview-body" id="previewBody">
                Loading...
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileLinks = document.querySelectorAll('.file-link');
            
            fileLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const path = this.dataset.path;
                    showFilePreview(path);
                });
            });
        });
        
        function showFilePreview(path) {
            const modal = document.getElementById('filePreviewModal');
            const title = document.getElementById('previewTitle');
            const body = document.getElementById('previewBody');
            
            modal.classList.add('active');
            body.innerHTML = '<p>Loading...</p>';
            
            fetch('{{ route("file-manager.show") }}?path=' + encodeURIComponent(path))
                .then(response => response.json())
                .then(data => {
                    title.textContent = data.name;
                    
                    let html = '<div class="file-info">';
                    html += '<div class="file-info-item"><span class="file-info-label">Size:</span> <span class="file-info-value">' + (data.size / 1024).toFixed(2) + ' KB</span></div>';
                    html += '<div class="file-info-item"><span class="file-info-label">Extension:</span> <span class="file-info-value">' + (data.extension || 'none') + '</span></div>';
                    html += '<div class="file-info-item"><span class="file-info-label">Modified:</span> <span class="file-info-value">' + new Date(data.modified * 1000).toLocaleString() + '</span></div>';
                    html += '</div>';
                    
                    if (data.can_preview && data.content) {
                        html += '<div class="file-preview-code">' + escapeHtml(data.content) + '</div>';
                    } else {
                        html += '<p style="color: #7f8c8d;">Preview not available for this file type or file is too large.</p>';
                    }
                    
                    body.innerHTML = html;
                })
                .catch(error => {
                    body.innerHTML = '<p style="color: #e74c3c;">Error loading file: ' + error.message + '</p>';
                });
        }
        
        function closePreview() {
            const modal = document.getElementById('filePreviewModal');
            modal.classList.remove('active');
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePreview();
            }
        });
        
        // Close modal on background click
        document.getElementById('filePreviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePreview();
            }
        });
    </script>
</body>
</html>
