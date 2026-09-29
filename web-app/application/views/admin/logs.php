<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: #0f172a;">AI Observability & Multi-Agent Traces</h3>
        <p class="text-muted mb-0">Inspect real-time supervisor decisions, tool calls, retrieved chunks, latency profiles, and token usage.</p>
    </div>
</div>

<div class="aegis-card">
    <div class="table-responsive">
        <table class="table aegis-table">
            <thead>
                <tr>
                    <th>Trace #</th>
                    <th>User Query</th>
                    <th>Agent Selected</th>
                    <th>Tools Executed</th>
                    <th>Retrieved Sources & Scores</th>
                    <th>Latency</th>
                    <th>Tokens</th>
                    <th>Model</th>
                    <th>Rating</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="10" class="text-center py-5 text-muted">No agent execution traces recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td class="font-monospace text-muted small">#TR-<?php echo $l->id; ?></td>
                            <td>
                                <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($l->query); ?>">
                                    <?php echo htmlspecialchars($l->query); ?>
                                </div>
                                <small class="text-muted"><?php echo htmlspecialchars($l->full_name ?: 'System Session'); ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border font-monospace" style="font-size: 11px;">
                                    <?php echo htmlspecialchars($l->agent_selected); ?>
                                </span>
                            </td>
                            <td>
                                <span class="small font-monospace text-dark bg-light px-2 py-1 rounded border">
                                    <?php echo htmlspecialchars($l->tools_used ?: '[]'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($l->retrieved_documents ?: 'None'); ?>">
                                    <i class="bi bi-file-earmark-text text-primary me-1"></i><?php echo htmlspecialchars($l->retrieved_documents ?: 'None'); ?>
                                </div>
                            </td>
                            <td class="small fw-semibold"><?php echo $l->latency_ms; ?> ms</td>
                            <td class="small text-muted font-monospace"><?php echo $l->token_usage; ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($l->model); ?></span></td>
                            <td>
                                <?php if ($l->feedback === 'HELPFUL'): ?>
                                    <span class="badge bg-success-subtle text-success border"><i class="bi bi-hand-thumbs-up"></i></span>
                                <?php elseif ($l->feedback === 'NOT_HELPFUL'): ?>
                                    <span class="badge bg-danger-subtle text-danger border"><i class="bi bi-hand-thumbs-down"></i></span>
                                <?php else: ?>
                                    <span class="text-muted small">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted text-nowrap"><?php echo date('M d, H:i:s', strtotime($l->created_at)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
