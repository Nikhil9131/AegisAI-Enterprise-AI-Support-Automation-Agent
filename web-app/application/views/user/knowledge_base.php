<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">Enterprise Knowledge Base</h3>
        <p class="text-muted mb-0">Searchable corporate policies, network guides, and technical standards indexed in Qdrant.</p>
    </div>
    <a href="<?php echo base_url('knowledge/search'); ?>" class="btn btn-aegis-primary">
        <i class="bi bi-search me-1"></i> Semantic Search
    </a>
</div>

<!-- Search Input Hero -->
<div class="aegis-card p-4 mb-4 text-center" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);">
    <h4 class="fw-bold text-dark mb-2">Search Corporate Documentation</h4>
    <p class="text-muted small mb-3">AI-powered semantic retrieval finds policies by meaning, not just keywords.</p>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="<?php echo base_url('knowledge/search'); ?>" method="GET">
                <div class="input-group input-group-lg shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="e.g. laptop refresh cycle, VPN certificate renewal, parental leave..." required>
                    <button type="submit" class="btn btn-aegis-primary px-4">Search Knowledge</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Category Filter Pills -->
<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="<?php echo base_url('knowledge'); ?>" class="btn btn-sm <?php echo empty($selected_cat) ? 'btn-primary' : 'btn-outline-secondary bg-white'; ?> rounded-pill px-3">
        All Categories
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="<?php echo base_url('knowledge?cat=' . $cat->id); ?>" class="btn btn-sm <?php echo ($selected_cat == $cat->id) ? 'btn-primary' : 'btn-outline-secondary bg-white'; ?> rounded-pill px-3">
            <?php echo htmlspecialchars($cat->name); ?> (<?php echo $cat->doc_count; ?>)
        </a>
    <?php endforeach; ?>
</div>

<!-- Documents Grid -->
<div class="row g-3">
    <?php if (empty($documents)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-folder2-open fs-2 d-block mb-2"></i>
            No documents found in this category.
        </div>
    <?php else: ?>
        <?php foreach ($documents as $doc): ?>
            <div class="col-md-6 col-lg-4">
                <div class="aegis-card h-100 mb-0 d-flex flex-column justify-content-between p-3">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 11px;">
                                <?php echo htmlspecialchars($doc->category_name ?: 'General'); ?>
                            </span>
                            <span class="badge bg-light text-muted border" style="font-size: 10px;">
                                <?php echo htmlspecialchars($doc->file_type); ?>
                            </span>
                        </div>
                        <h6 class="fw-bold mb-2">
                            <a href="<?php echo base_url('knowledge/view_doc/' . $doc->id); ?>" class="text-dark text-decoration-none">
                                <?php echo htmlspecialchars($doc->title); ?>
                            </a>
                        </h6>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-file-earmark-text me-1"></i> <?php echo htmlspecialchars($doc->file_name); ?>
                        </p>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="text-muted" style="font-size: 11px;">
                            <i class="bi bi-layers text-primary me-1"></i> <?php echo $doc->chunk_count; ?> Chunks Indexed
                        </span>
                        <a href="<?php echo base_url('knowledge/view_doc/' . $doc->id); ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11.5px;">
                            Read Policy &rarr;
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
