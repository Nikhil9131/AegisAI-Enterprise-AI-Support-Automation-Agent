<div class="mb-4">
    <a href="<?php echo base_url('knowledge'); ?>" class="small text-muted text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i> Back to Knowledge Base
    </a>
    <h3 class="fw-bold mt-1 mb-1" style="color: #0f172a;">Semantic Knowledge Search</h3>
    <p class="text-muted mb-0">Querying enterprise documents using dense vector embeddings in Qdrant.</p>
</div>

<!-- Search Bar -->
<div class="aegis-card p-3 mb-4">
    <form action="<?php echo base_url('knowledge/search'); ?>" method="GET" class="d-flex gap-2">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-primary"></i></span>
            <input type="text" name="q" class="form-control border-start-0" placeholder="Ask a question or enter keywords..." value="<?php echo htmlspecialchars($query); ?>" required>
        </div>
        <button type="submit" class="btn btn-aegis-primary px-4 text-nowrap">Search</button>
    </form>
    <div class="d-flex justify-content-between align-items-center mt-2 px-1">
        <span class="text-muted small">
            Engine: <span class="badge bg-primary-subtle text-primary border"><?php echo $search_mode; ?></span>
        </span>
        <span class="text-muted small">Showing top matching chunks with cosine similarity</span>
    </div>
</div>

<!-- Search Results -->
<?php if (!empty($query)): ?>
    <h6 class="fw-bold text-muted text-uppercase mb-3" style="font-size: 12px;">
        Search Results for "<?php echo htmlspecialchars($query); ?>"
    </h6>

    <?php if (empty($results)): ?>
        <div class="aegis-card p-5 text-center text-muted">
            <i class="bi bi-search fs-2 d-block mb-2"></i>
            <h6>No matching documentation chunks found.</h6>
            <p class="small text-muted mb-0">Try different search terms or ask the <a href="<?php echo base_url('assistant'); ?>">AI Assistant</a>.</p>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($results as $res): ?>
                <?php 
                    $doc_name = is_array($res) ? ($res['document_name'] ?? ($res['title'] ?? 'Enterprise Policy')) : ($res->title ?? 'Document');
                    $content_chunk = is_array($res) ? ($res['content'] ?? ($res['text'] ?? '')) : ($res->file_name ?? '');
                    $score = is_array($res) ? ($res['score'] ?? 0.88) : 0.85;
                    $page = is_array($res) ? ($res['page_number'] ?? ($res['page'] ?? 1)) : 1;
                ?>
                <div class="aegis-card p-3 mb-0">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($doc_name); ?></span>
                            <span class="badge bg-light text-muted border" style="font-size: 10.5px;">Page <?php echo $page; ?></span>
                        </div>
                        <span class="badge bg-success-subtle text-success border">
                            <i class="bi bi-graph-up me-1"></i> Match Score: <?php echo round($score * 100, 1); ?>%
                        </span>
                    </div>
                    <div class="p-3 bg-light rounded-2 small text-secondary mb-2" style="line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars(substr($content_chunk, 0, 450))); ?><?php echo strlen($content_chunk) > 450 ? '...' : ''; ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span class="text-muted" style="font-size: 11px;">Verified Aegis Knowledge Base Chunk</span>
                        <a href="<?php echo base_url('assistant'); ?>" class="btn btn-sm btn-link text-decoration-none p-0 small">
                            <i class="bi bi-chat-text me-1"></i> Discuss with AI &rarr;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
