<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Knowledge Base Management</h3>
        <p class="text-muted mb-0">Organize enterprise documentation categories, mapping, and taxonomy.</p>
    </div>
    <button class="btn btn-aegis-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
        <i class="bi bi-folder-plus me-1"></i> New Category
    </button>
</div>

<div class="row g-3">
    <?php foreach ($categories as $cat): ?>
        <div class="col-md-6 col-lg-4">
            <div class="aegis-card p-3 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="brand-icon-box" style="width: 36px; height: 36px; font-size: 18px; border-radius: 8px;">
                            <i class="bi bi-folder2"></i>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border"><?php echo $cat->doc_count; ?> Documents</span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($cat->name); ?></h6>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($cat->description ?: 'Enterprise policy and documentation group.'); ?></p>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="font-monospace text-muted small">/<?php echo htmlspecialchars($cat->slug); ?></span>
                    <a href="<?php echo base_url('knowledge?cat=' . $cat->id); ?>" class="btn btn-sm btn-link text-decoration-none p-0 small">
                        View Docs &rarr;
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal: New Category -->
<div class="modal fade" id="newCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo base_url('admin/create_category'); ?>" method="POST">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Create Knowledge Category</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Category Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g. Cloud Security & Compliance">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Explain the scope of documents in this category..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-aegis-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
