<div class="mb-4">
    <a href="<?php echo base_url('knowledge'); ?>" class="small text-muted text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to Knowledge Base
    </a>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div>
            <h3 class="fw-bold mb-1" style="color: #0f172a;"><?php echo htmlspecialchars($doc->title); ?></h3>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border"><?php echo htmlspecialchars($doc->category_name ?? 'General'); ?></span>
                <span class="badge bg-light text-muted border"><?php echo htmlspecialchars($doc->file_name); ?></span>
                <span class="text-muted small">&bull; <?php echo $doc->chunk_count; ?> Chunks in Vector Store</span>
            </div>
        </div>
        <a href="<?php echo base_url('assistant'); ?>" class="btn btn-aegis-primary btn-sm">
            <i class="bi bi-robot me-1"></i> Ask AI About This Doc
        </a>
    </div>
</div>

<div class="aegis-card">
    <div class="aegis-card-body p-4">
        <pre class="p-3 bg-light rounded-2 text-dark border" style="white-space: pre-wrap; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 14px; line-height: 1.7;"><?php echo htmlspecialchars($content); ?></pre>
    </div>
</div>
