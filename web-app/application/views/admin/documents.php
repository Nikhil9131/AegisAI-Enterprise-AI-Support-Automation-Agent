<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Document Vault & Dense Vector Indexing</h3>
        <p class="text-muted mb-0">Upload enterprise documents (PDF, DOCX, TXT) for chunking and ingestion into Qdrant.</p>
    </div>
    <button class="btn btn-aegis-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
        <i class="bi bi-cloud-arrow-up me-1"></i> Ingest New Document
    </button>
</div>

<!-- Documents Table -->
<div class="aegis-card">
    <div class="table-responsive">
        <table class="table aegis-table">
            <thead>
                <tr>
                    <th>Title & Filename</th>
                    <th>Category</th>
                    <th>Format</th>
                    <th>Size</th>
                    <th>Vector Chunks</th>
                    <th>Uploader</th>
                    <th>Status</th>
                    <th>Indexed Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $d): ?>
                    <tr>
                        <td>
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($d->title); ?></div>
                            <small class="text-muted font-monospace"><i class="bi bi-file-earmark me-1"></i><?php echo htmlspecialchars($d->file_name); ?></small>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($d->category_name ?? 'General'); ?></span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary"><?php echo htmlspecialchars($d->file_type); ?></span></td>
                        <td class="small text-muted"><?php echo round($d->file_size / 1024, 1); ?> KB</td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border">
                                <i class="bi bi-layers me-1"></i> <?php echo $d->chunk_count; ?> chunks
                            </span>
                        </td>
                        <td class="small text-muted"><?php echo htmlspecialchars($d->uploader_name ?: 'System Admin'); ?></td>
                        <td>
                            <span class="badge <?php echo ($d->status === 'INDEXED') ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'; ?> border">
                                <i class="bi bi-check-circle me-1"></i> <?php echo $d->status; ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?php echo date('M d, Y', strtotime($d->created_at)); ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="<?php echo base_url('knowledge/view_doc/' . $d->id); ?>" class="btn btn-sm btn-outline-primary py-0 px-2" title="Inspect">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo base_url('admin/delete_document/' . $d->id); ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Remove this document and purge its vector embeddings?')" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Ingest New Document -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo base_url('admin/upload_document'); ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up text-primary me-2"></i> Ingest Document into Vector Store</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Document Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Incident Response & Data Breach Playbook 2026">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Target Knowledge Category</label>
                        <select name="category_id" class="form-select">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">File Upload (PDF, DOCX, TXT)</label>
                        <input type="file" name="doc_file" class="form-control" required accept=".pdf,.docx,.txt">
                        <div class="form-text small text-muted">The file will be extracted, cleaned, chunked into 500-token semantic passages, and embedded into Qdrant.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-aegis-primary">Upload & Trigger Vector Indexing</button>
                </div>
            </form>
        </div>
    </div>
</div>
